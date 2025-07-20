<?php
/**
 * Mail Migration Tool - Configuration
 * Configuración para la herramienta de migración de correos IMAP
 */

return [
    // Debug mode
    'debug' => true,
    
    // Timeout settings (seconds)
    'imap_timeout' => 30,
    'connection_timeout' => 15,
    
    // Migration settings
    'batch_size' => 50, // Number of emails to process per batch
    'preserve_flags' => true, // Maintain read/unread status
    'preserve_structure' => true, // Maintain folder hierarchy
    
    // UI Settings
    'app_name' => 'Migrabox',
    'app_version' => '1.0.0',
    'theme' => 'default',
    
    // Security
    'session_timeout' => 3600, // 1 hour
    'max_execution_time' => 0, // Unlimited for large migrations
    
    // Logging
    'log_level' => 'info',
    'log_file' => 'logs/migration.log',
    
    // Default IMAP ports
    'default_ports' => [
        'imap' => 143,
        'imaps' => 993
    ]
]; 