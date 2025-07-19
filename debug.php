<?php
/**
 * Debug Script - Para identificar el problema de JSON
 */

// Capturar cualquier output
ob_start();

// Verificar si vendor/autoload.php existe
echo "DEBUG: Checking vendor/autoload.php...\n";
if (file_exists('vendor/autoload.php')) {
    echo "DEBUG: vendor/autoload.php EXISTS\n";
} else {
    echo "DEBUG: vendor/autoload.php DOES NOT EXIST\n";
}

// Verificar si config existe
echo "DEBUG: Checking config/config.php...\n";
if (file_exists('config/config.php')) {
    echo "DEBUG: config/config.php EXISTS\n";
} else {
    echo "DEBUG: config/config.php DOES NOT EXIST\n";
}

// Intentar cargar config
try {
    echo "DEBUG: Loading config...\n";
    $config = require_once 'config/config.php';
    echo "DEBUG: Config loaded successfully\n";
} catch (Exception $e) {
    echo "DEBUG: Error loading config: " . $e->getMessage() . "\n";
}

// Verificar extensión IMAP
echo "DEBUG: Checking IMAP extension...\n";
if (extension_loaded('imap')) {
    echo "DEBUG: IMAP extension is loaded\n";
} else {
    echo "DEBUG: IMAP extension is NOT loaded\n";
}

// Verificar Composer
echo "DEBUG: Checking Composer...\n";
if (file_exists('composer.json')) {
    echo "DEBUG: composer.json exists\n";
} else {
    echo "DEBUG: composer.json does NOT exist\n";
}

if (is_dir('vendor')) {
    echo "DEBUG: vendor directory exists\n";
} else {
    echo "DEBUG: vendor directory does NOT exist\n";
}

// Capturar el output
$debug_output = ob_get_clean();

// Ahora devolver como JSON
header('Content-Type: application/json');
echo json_encode([
    'debug_info' => $debug_output,
    'php_version' => PHP_VERSION,
    'current_directory' => getcwd(),
    'files_in_root' => scandir('.'),
    'success' => true
]);
?> 