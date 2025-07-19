<?php
/**
 * Mail Migration Tool - Connection Test
 * Prueba de conexión a servidores IMAP
 */

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration and autoloader
$config = require_once 'config/config.php';

// Check if vendor directory exists (composer dependencies installed)
if (!file_exists('vendor/autoload.php')) {
    echo json_encode([
        'success' => false,
        'message' => '❌ Dependencias no instaladas.\n\nPasos para instalar:\n1. Instalar Composer (getcomposer.org)\n2. Ejecutar: composer install\n3. Subir carpeta vendor/ al servidor'
    ]);
    exit;
}

require_once 'vendor/autoload.php';
require_once 'src/ImapConnector.php';

use MailMigration\ImapConnector;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
    exit;
}

try {
    // Get form data
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
        echo json_encode([
            'success' => false,
            'message' => implode('\n', $errors)
        ]);
        exit;
    }
    
    // Initialize IMAP connector
    $connector = new ImapConnector($config);
    
    // Test source connection
    $sourceResult = $connector->testConnection($sourceConfig);
    if (!$sourceResult['success']) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Error en servidor ORIGEN:\n" . $sourceResult['message']
        ]);
        exit;
    }
    
    // Test destination connection
    $destResult = $connector->testConnection($destConfig);
    if (!$destResult['success']) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Error en servidor DESTINO:\n" . $destResult['message']
        ]);
        exit;
    }
    
    // Both connections successful
    $totalSourceMessages = $sourceResult['message_count'];
    $totalDestMessages = $destResult['message_count'];
    $totalSourceMailboxes = $sourceResult['mailbox_count'];
    $totalDestMailboxes = $destResult['mailbox_count'];
    
    echo json_encode([
        'success' => true,
        'message' => "✅ ¡Ambas conexiones exitosas!\n\n📊 RESUMEN:\n" .
                    "• Origen: {$totalSourceMailboxes} carpetas, {$totalSourceMessages} emails en INBOX\n" .
                    "• Destino: {$totalDestMailboxes} carpetas, {$totalDestMessages} emails en INBOX\n\n" .
                    "🚀 ¡Listo para migración!",
        'source_details' => $sourceResult,
        'destination_details' => $destResult
    ]);

} catch (Exception $e) {
    // Log error
    $logFile = $config['log_file'] ?? 'logs/migration.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] TEST_CONNECTION_ERROR: " . $e->getMessage() . PHP_EOL;
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false,
        'message' => "❌ Error interno:\n" . $e->getMessage()
    ]);
}
?> 