<?php
/**
 * Mail Migration Tool - Migration Handler
 * Manejador principal de migración de correos
 */

// Clean any existing output buffer and disable error display to avoid JSON corruption
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Disable error display to prevent HTML error messages from corrupting JSON
ini_set('display_errors', 0);
error_reporting(0);

// Set proper headers
header('Content-Type: application/json');

// Load configuration
$config = require_once 'config/config.php';

// Check if dependencies are installed
if (!file_exists('vendor/autoload.php')) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => '❌ Dependencias no instaladas. Ejecutar: composer install'
    ]);
    exit;
}

require_once 'vendor/autoload.php';
require_once 'src/ImapConnector.php';

use MailMigration\ImapConnector;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
    exit;
}

try {
    // Get form data and validate
    $sourceConfig = [
        'host' => $_POST['source_host'] ?? '',
        'port' => (int)($_POST['source_port'] ?? 993),
        'ssl' => isset($_POST['source_ssl']) && $_POST['source_ssl'] === 'on',
        'username' => $_POST['source_username'] ?? '',
        'password' => $_POST['source_password'] ?? ''
    ];
    
    $destConfig = [
        'host' => $_POST['dest_host'] ?? '',
        'port' => (int)($_POST['dest_port'] ?? 993),
        'ssl' => isset($_POST['dest_ssl']) && $_POST['dest_ssl'] === 'on',
        'username' => $_POST['dest_username'] ?? '',
        'password' => $_POST['dest_password'] ?? ''
    ];
    
    // Migration options
    $options = [
        'preserve_flags' => isset($_POST['preserve_flags']) && $_POST['preserve_flags'] === 'on',
        'preserve_structure' => isset($_POST['preserve_structure']) && $_POST['preserve_structure'] === 'on',
        'batch_size' => (int)($_POST['batch_size'] ?? $config['batch_size'])
    ];
    
    // Validate required fields
    $requiredFields = ['host', 'username', 'password'];
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (empty($sourceConfig[$field])) {
            $errors[] = "Campo requerido faltante en origen: {$field}";
        }
        if (empty($destConfig[$field])) {
            $errors[] = "Campo requerido faltante en destino: {$field}";
        }
    }
    
    if (!empty($errors)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => implode('\n', $errors)
        ]);
        exit;
    }
    
    // Initialize IMAP connector
    $connector = new ImapConnector($config);
    
    // Step 1: Test connections first
    $sourceTest = $connector->testConnection($sourceConfig);
    if (!$sourceTest['success']) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Error conectando al servidor origen:\n" . $sourceTest['message']
        ]);
        exit;
    }
    
    $destTest = $connector->testConnection($destConfig);
    if (!$destTest['success']) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Error conectando al servidor destino:\n" . $destTest['message']
        ]);
        exit;
    }
    
    // Step 2: Connect to both servers
    if (!$connector->connectSource($sourceConfig)) {
        echo json_encode([
            'success' => false,
            'message' => '❌ Error estableciendo conexión con servidor origen'
        ]);
        exit;
    }
    
    if (!$connector->connectDestination($destConfig)) {
        echo json_encode([
            'success' => false,
            'message' => '❌ Error estableciendo conexión con servidor destino'
        ]);
        exit;
    }
    
    // Step 3: Get mailboxes from source
    $sourceMailboxes = $connector->getSourceMailboxes();
    $totalMailboxes = count($sourceMailboxes);
    
    if ($totalMailboxes === 0) {
        echo json_encode([
            'success' => false,
            'message' => '❌ No se encontraron carpetas en el servidor origen'
        ]);
        $connector->closeConnections();
        exit;
    }
    
    // Step 4: Start migration process
    $migrationResults = [];
    $totalMessages = 0;
    $totalMigrated = 0;
    $totalErrors = 0;
    $processedMailboxes = 0;
    
    foreach ($sourceMailboxes as $mailbox) {
        $mailboxName = $mailbox['name'];
        $messageCount = $mailbox['count'];
        
        // Skip empty mailboxes
        if ($messageCount === 0) {
            $processedMailboxes++;
            continue;
        }
        
        $totalMessages += $messageCount;
        
        // Create destination mailbox if needed and preserve_structure is enabled
        if ($options['preserve_structure']) {
            $connector->createDestinationMailbox($mailboxName);
            $destinationMailbox = $mailboxName;
        } else {
            // Migrate everything to INBOX if not preserving structure
            $destinationMailbox = 'INBOX';
        }
        
        // Migrate this mailbox
        $result = $connector->migrateMailbox(
            $mailboxName, 
            $destinationMailbox, 
            $options['batch_size'], 
            $options['preserve_flags']
        );
        
        $migrationResults[$mailboxName] = $result;
        
        if ($result['success']) {
            $totalMigrated += $result['migrated'];
            $totalErrors += $result['errors'];
        }
        
        $processedMailboxes++;
        
        // For now, we'll complete the migration synchronously
        // In a future version, this could be done asynchronously with progress updates
    }
    
    // Close connections
    $connector->closeConnections();
    
    // Step 5: Return results
    $successfulMailboxes = array_filter($migrationResults, function($result) {
        return $result['success'];
    });
    
    $failedMailboxes = array_filter($migrationResults, function($result) {
        return !$result['success'];
    });
    
    $overallSuccess = count($failedMailboxes) === 0;
    
    // Clean buffer and output JSON
    ob_clean();
    echo json_encode([
        'success' => $overallSuccess,
        'message' => $overallSuccess 
            ? "✅ ¡Migración completada exitosamente!\n\n📊 RESUMEN:\n" .
              "• Carpetas procesadas: {$processedMailboxes}/{$totalMailboxes}\n" .
              "• Mensajes migrados: {$totalMigrated}/{$totalMessages}\n" .
              "• Errores: {$totalErrors}"
            : "⚠️ Migración completada con algunos errores\n\n📊 RESUMEN:\n" .
              "• Carpetas exitosas: " . count($successfulMailboxes) . "/{$totalMailboxes}\n" .
              "• Carpetas fallidas: " . count($failedMailboxes) . "\n" .
              "• Mensajes migrados: {$totalMigrated}/{$totalMessages}\n" .
              "• Errores: {$totalErrors}",
        'details' => [
            'total_mailboxes' => $totalMailboxes,
            'processed_mailboxes' => $processedMailboxes,
            'total_messages' => $totalMessages,
            'migrated_messages' => $totalMigrated,
            'error_count' => $totalErrors,
            'successful_mailboxes' => count($successfulMailboxes),
            'failed_mailboxes' => count($failedMailboxes),
            'migration_results' => $migrationResults
        ]
    ]);

} catch (Exception $e) {
    // Clean up connections
    if (isset($connector)) {
        $connector->closeConnections();
    }
    
    // Log error
    $logFile = $config['log_file'] ?? 'logs/migration.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] MIGRATION_ERROR: " . $e->getMessage() . PHP_EOL;
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => "❌ Error interno durante migración:\n" . $e->getMessage()
    ]);
}
?> 