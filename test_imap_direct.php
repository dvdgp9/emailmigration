<?php
/**
 * Direct IMAP Test - Prueba directa sin AJAX
 */

// Disable all output buffering and error display to see raw output
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Direct IMAP Test</h1>\n";
echo "<pre>\n";

echo "1. Checking if vendor/autoload.php exists: ";
if (file_exists('vendor/autoload.php')) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
    exit;
}

echo "2. Loading autoloader: ";
try {
    require_once 'vendor/autoload.php';
    echo "✅ SUCCESS\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

echo "3. Loading config: ";
try {
    $config = require_once 'config/config.php';
    echo "✅ SUCCESS\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

echo "4. Loading ImapConnector class: ";
try {
    require_once 'src/ImapConnector.php';
    echo "✅ SUCCESS\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

echo "5. Instantiating ImapConnector: ";
try {
    use MailMigration\ImapConnector;
    $connector = new ImapConnector($config);
    echo "✅ SUCCESS\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

echo "6. Testing with dummy credentials (should fail but not crash): ";
try {
    $dummyConfig = [
        'host' => 'test.example.com',
        'port' => 993,
        'ssl' => true,
        'username' => 'test@example.com',
        'password' => 'dummy'
    ];
    
    $result = $connector->testConnection($dummyConfig);
    echo "✅ METHOD WORKS - Result: " . ($result['success'] ? 'Success' : 'Failed as expected') . "\n";
    echo "Message: " . $result['message'] . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n7. Checking what test_connection.php actually returns:\n";
echo "=== START RAW OUTPUT FROM test_connection.php ===\n";

// Capture the actual output from test_connection.php
ob_start();

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'source_host' => 'test.example.com',
    'source_port' => '993',
    'source_ssl' => 'on',
    'source_username' => 'test@example.com',
    'source_password' => 'dummy',
    'dest_host' => 'test2.example.com',
    'dest_port' => '993',
    'dest_ssl' => 'on',
    'dest_username' => 'test2@example.com',
    'dest_password' => 'dummy'
];

// Include test_connection.php and capture its output
include 'test_connection.php';

$output = ob_get_clean();
echo $output;
echo "\n=== END RAW OUTPUT ===\n";

echo "\n8. Analysis:\n";
echo "Output length: " . strlen($output) . " bytes\n";
echo "First 100 characters: " . substr($output, 0, 100) . "\n";
echo "Is valid JSON? ";
$jsonData = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ YES\n";
} else {
    echo "❌ NO - JSON Error: " . json_last_error_msg() . "\n";
}

echo "\n</pre>";
?> 