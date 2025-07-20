<?php
/**
 * Mail Migration Tool - Migration Handler DEBUG VERSION
 * Version without error suppression to see the real error
 */

// ENABLE error display to see what's failing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Clean any existing output buffer
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Set timeouts for migration process
set_time_limit(300); // 5 minutes max
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

// Set proper headers
header('Content-Type: application/json');

echo "DEBUG: Starting migrate_debug.php\n";

// Load configuration
echo "DEBUG: Loading config...\n";
$config = require_once 'config/config.php';
echo "DEBUG: Config loaded\n";

// Check if dependencies are installed
echo "DEBUG: Checking vendor/autoload.php...\n";
if (!file_exists('vendor/autoload.php')) {
    echo "ERROR: vendor/autoload.php not found\n";
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => '❌ Dependencies not installed'
    ]);
    exit;
}
echo "DEBUG: vendor/autoload.php exists\n";

echo "DEBUG: Loading autoloader...\n";
require_once 'vendor/autoload.php';
echo "DEBUG: Autoloader loaded\n";

echo "DEBUG: Loading ImapConnector...\n";
require_once 'src/ImapConnector.php';
echo "DEBUG: ImapConnector file loaded\n";

echo "DEBUG: Importing namespace...\n";
use MailMigration\ImapConnector;
echo "DEBUG: Namespace imported\n";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
    exit;
}

echo "DEBUG: POST request confirmed\n";

try {
    echo "DEBUG: Starting try block\n";
    
    // Get form data and validate
    echo "DEBUG: Getting form data...\n";
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
    
    echo "DEBUG: Form data extracted\n";
    
    // Migration options
    $options = [
        'preserve_flags' => isset($_POST['preserve_flags']) && $_POST['preserve_flags'] === 'on',
        'preserve_structure' => isset($_POST['preserve_structure']) && $_POST['preserve_structure'] === 'on',
        'batch_size' => (int)($_POST['batch_size'] ?? $config['batch_size'])
    ];
    
    echo "DEBUG: Options configured\n";
    
    // Validate required fields
    $requiredFields = ['host', 'username', 'password'];
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (empty($sourceConfig[$field])) {
            $errors[] = "Missing source field: {$field}";
        }
        if (empty($destConfig[$field])) {
            $errors[] = "Missing destination field: {$field}";
        }
    }
    
    if (!empty($errors)) {
        echo "DEBUG: Validation errors found\n";
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => implode('\n', $errors)
        ]);
        exit;
    }
    
    echo "DEBUG: Validation passed\n";
    
    // Initialize IMAP connector
    echo "DEBUG: Creating ImapConnector...\n";
    $connector = new ImapConnector($config);
    echo "DEBUG: ImapConnector created successfully\n";
    
    // Test source connection (this should work based on test_connection.php)
    echo "DEBUG: Testing source connection...\n";
    $sourceTest = $connector->testConnection($sourceConfig);
    echo "DEBUG: Source test result: " . ($sourceTest['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    
    if (!$sourceTest['success']) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "❌ Source connection failed: " . $sourceTest['message']
        ]);
        exit;
    }
    
    // Test destination connection  
    echo "DEBUG: Testing dest connection...\n";
    $destTest = $connector->testConnection($destConfig);
    echo "DEBUG: Dest test result: " . ($destTest['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    
    if (!$destTest['success']) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "❌ Destination connection failed: " . $destTest['message']
        ]);
        exit;
    }
    
    // Establish persistent connections  
    echo "DEBUG: Establishing source connection...\n";
    $sourceConnected = $connector->connectSource($sourceConfig);
    echo "DEBUG: Source connected: " . ($sourceConnected ? 'YES' : 'NO') . "\n";
    
    if (!$sourceConnected) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "❌ Failed to establish source connection"
        ]);
        exit;
    }
    
    echo "DEBUG: Establishing dest connection...\n";
    $destConnected = $connector->connectDestination($destConfig);
    echo "DEBUG: Dest connected: " . ($destConnected ? 'YES' : 'NO') . "\n";
    
    if (!$destConnected) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "❌ Failed to establish destination connection"
        ]);
        exit;
    }
    
    // Get mailboxes
    echo "DEBUG: Getting source mailboxes...\n";
    $sourceMailboxes = $connector->getSourceMailboxes();
    echo "DEBUG: Found " . count($sourceMailboxes) . " mailboxes\n";
    
    if (count($sourceMailboxes) === 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "❌ No mailboxes found in source"
        ]);
        exit;
    }
    
    // Try migration with FIRST mailbox only and VERY small limit
    echo "DEBUG: Starting migration test...\n";
    $firstMailbox = $sourceMailboxes[0];
    echo "DEBUG: Migrating mailbox: " . $firstMailbox['name'] . " (" . $firstMailbox['count'] . " messages)\n";
    
    // THIS IS PROBABLY WHERE IT FAILS - let's see
    echo "DEBUG: About to call migrateMailbox()...\n";
    $migrationResult = $connector->migrateMailbox($firstMailbox['name'], $firstMailbox['name'], 1, false);
    echo "DEBUG: migrateMailbox() completed\n";
    
    // Close connections
    echo "DEBUG: Closing connections...\n";
    $connector->closeConnections();
    echo "DEBUG: Connections closed\n";
    
    // Return success
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => '✅ Full migration test completed successfully!\n' . 
                    'Migration result: ' . ($migrationResult['success'] ? 'SUCCESS' : 'FAILED') . '\n' .
                    'Details: ' . $migrationResult['message'],
        'debug_info' => [
            'mailboxes_found' => count($sourceMailboxes),
            'migration_result' => $migrationResult
        ]
    ]);
    
} catch (Exception $e) {
    echo "DEBUG: Exception caught: " . $e->getMessage() . "\n";
    echo "DEBUG: Exception trace: " . $e->getTraceAsString() . "\n";
    
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => "❌ Exception: " . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    echo "DEBUG: Fatal error caught: " . $e->getMessage() . "\n";
    echo "DEBUG: Error trace: " . $e->getTraceAsString() . "\n";
    
    ob_clean();  
    echo json_encode([
        'success' => false,
        'message' => "❌ Fatal Error: " . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?> 