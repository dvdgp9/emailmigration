<?php
/**
 * Mail Migration Tool - Migration Handler
 * Manejador principal de migración de correos
 */

// Set proper headers
header('Content-Type: application/json');

// Load configuration
$config = require_once 'config/config.php';

// Check if dependencies are installed
if (!file_exists('vendor/autoload.php')) {
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
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
    exit;
}

try {
    // This will be implemented in Task 2.4: Desarrollar funcionalidad de migración básica
    // For now, return a placeholder response indicating future implementation
    
    echo json_encode([
        'success' => false,
        'message' => '⏳ Funcionalidad de migración será implementada en Task 2.4.\n\n' .
                    'Actualmente disponible:\n' .
                    '✅ Prueba de conexiones IMAP\n' .
                    '✅ Validación de credenciales\n' .
                    '🔄 Migración de mensajes (próximamente)'
    ]);
    
} catch (Exception $e) {
    // Log error
    $logFile = $config['log_file'] ?? 'logs/migration.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] MIGRATION_ERROR: " . $e->getMessage() . PHP_EOL;
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false,
        'message' => "❌ Error interno: " . $e->getMessage()
    ]);
}
?> 