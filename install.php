<?php
/**
 * Mail Migration Tool - Installation Helper
 * Verificador de requisitos y guía de instalación
 */

echo "📧 Mail Migration Tool - Installation Helper\n";
echo "============================================\n\n";

// Check PHP version
echo "1. Verificando versión de PHP...\n";
$phpVersion = PHP_VERSION;
$requiredPhpVersion = '7.4.0';

if (version_compare($phpVersion, $requiredPhpVersion, '>=')) {
    echo "   ✅ PHP {$phpVersion} (requerido: {$requiredPhpVersion}+)\n\n";
} else {
    echo "   ❌ PHP {$phpVersion} - Se requiere {$requiredPhpVersion}+\n\n";
}

// Check IMAP extension
echo "2. Verificando extensión IMAP de PHP...\n";
if (extension_loaded('imap')) {
    echo "   ✅ Extensión IMAP habilitada\n\n";
} else {
    echo "   ❌ Extensión IMAP NO habilitada\n";
    echo "      Soluciones:\n";
    echo "      • En Ubuntu/Debian: sudo apt-get install php-imap\n";
    echo "      • En CentOS/RHEL: yum install php-imap\n";
    echo "      • En cPanel: Activar en 'Select PHP Extensions'\n\n";
}

// Check if Composer is installed
echo "3. Verificando Composer...\n";
$composerInstalled = false;

// Check if composer is globally available
exec('composer --version 2>&1', $output, $returnVar);
if ($returnVar === 0) {
    echo "   ✅ Composer instalado globalmente\n";
    echo "      Ejecutar: composer install\n\n";
    $composerInstalled = true;
} else {
    echo "   ⚠️  Composer no disponible globalmente\n";
    echo "      Opciones:\n";
    echo "      1. Instalar Composer: https://getcomposer.org/download/\n";
    echo "      2. Usar composer.phar local (ver instrucciones abajo)\n\n";
}

// Check vendor directory
echo "4. Verificando dependencias...\n";
if (is_dir('vendor')) {
    echo "   ✅ Carpeta vendor/ existe - Dependencias instaladas\n\n";
} else {
    echo "   ❌ Carpeta vendor/ no existe - Dependencias no instaladas\n";
    if ($composerInstalled) {
        echo "      Ejecutar: composer install\n\n";
    } else {
        echo "      Pasos para instalar:\n";
        echo "      1. Descargar composer.phar:\n";
        echo "         curl -sS https://getcomposer.org/installer | php\n";
        echo "      2. Instalar dependencias:\n";
        echo "         php composer.phar install\n\n";
    }
}

// Check permissions
echo "5. Verificando permisos de archivos...\n";
$logDir = 'logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

if (is_writable($logDir)) {
    echo "   ✅ Directorio logs/ escribible\n";
} else {
    echo "   ❌ Directorio logs/ no escribible\n";
    echo "      Ejecutar: chmod 755 logs/\n";
}

if (is_readable('config/config.php')) {
    echo "   ✅ Archivo config/config.php legible\n\n";
} else {
    echo "   ❌ Archivo config/config.php no legible\n\n";
}

// Summary
echo "📋 RESUMEN DE INSTALACIÓN:\n";
echo "==========================\n";

$allRequirementsMet = version_compare($phpVersion, $requiredPhpVersion, '>=') && 
                      extension_loaded('imap') && 
                      is_dir('vendor') &&
                      is_writable($logDir);

if ($allRequirementsMet) {
    echo "✅ ¡Todos los requisitos están cumplidos!\n";
    echo "🚀 Tu webapp de migración de correos está lista para usar.\n\n";
    echo "Pasos siguientes:\n";
    echo "1. Subir todos los archivos a tu servidor web\n";
    echo "2. Acceder a index.php desde tu navegador\n";
    echo "3. Configurar las credenciales IMAP\n";
    echo "4. ¡Comenzar a migrar correos!\n\n";
} else {
    echo "⚠️  Algunos requisitos no están cumplidos.\n";
    echo "Por favor, sigue las instrucciones arriba para completar la instalación.\n\n";
    
    if (!is_dir('vendor')) {
        echo "🎯 ACCIÓN REQUERIDA: Instalar dependencias con Composer\n";
        if ($composerInstalled) {
            echo "   Ejecutar: composer install\n";
        } else {
            echo "   1. Instalar Composer desde https://getcomposer.org/\n";
            echo "   2. Ejecutar: composer install\n";
        }
    }
}

echo "🔗 Para soporte, consultar README.md\n";
?> 