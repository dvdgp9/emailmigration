<?php
/**
 * Installation Checker - Verificador de instalación
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Migration Tool - Installation Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .solution { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Mail Migration Tool - Verification</h1>
        
        <h2>1. PHP Version Check</h2>
        <?php
        $phpVersion = PHP_VERSION;
        $requiredPhp = '7.4.0';
        if (version_compare($phpVersion, $requiredPhp, '>=')): ?>
            <p class="status-ok">✅ PHP <?= $phpVersion ?> (Required: <?= $requiredPhp ?>+)</p>
        <?php else: ?>
            <p class="status-error">❌ PHP <?= $phpVersion ?> - Required: <?= $requiredPhp ?>+</p>
        <?php endif; ?>
        
        <h2>2. IMAP Extension Check</h2>
        <?php if (extension_loaded('imap')): ?>
            <p class="status-ok">✅ IMAP Extension is enabled</p>
        <?php else: ?>
            <p class="status-error">❌ IMAP Extension is NOT enabled</p>
            <div class="solution">
                <strong>🔧 Solution for IMAP:</strong><br>
                • In cPanel: Go to "Select PHP Extensions" and enable "imap"<br>
                • Contact your hosting provider to enable PHP IMAP extension
            </div>
        <?php endif; ?>
        
        <h2>3. Composer Dependencies Check</h2>
        <?php 
        $vendorExists = file_exists('vendor/autoload.php');
        $composerExists = file_exists('composer.json');
        ?>
        
        <?php if ($composerExists): ?>
            <p class="status-ok">✅ composer.json found</p>
        <?php else: ?>
            <p class="status-error">❌ composer.json not found</p>
        <?php endif; ?>
        
        <?php if ($vendorExists): ?>
            <p class="status-ok">✅ Composer dependencies installed (vendor/autoload.php exists)</p>
        <?php else: ?>
            <p class="status-error">❌ Composer dependencies NOT installed</p>
            <div class="solution">
                <strong>🔧 Solution for Composer Dependencies:</strong><br><br>
                
                <strong>Option 1: If you have SSH access to your server:</strong>
                <pre>cd /path/to/your/emailmigration/folder
composer install</pre>
                
                <strong>Option 2: Install locally and upload:</strong>
                <pre>1. Download the project to your computer
2. Install Composer on your computer (getcomposer.org)
3. Run: composer install
4. Upload the entire project including the "vendor" folder to your server</pre>
                
                <strong>Option 3: Download dependencies directly:</strong><br>
                <a href="install_dependencies.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📦 Auto-Install Dependencies</a>
            </div>
        <?php endif; ?>
        
        <h2>4. File Permissions Check</h2>
        <?php
        $logDir = 'logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        if (is_writable($logDir)): ?>
            <p class="status-ok">✅ Logs directory is writable</p>
        <?php else: ?>
            <p class="status-error">❌ Logs directory is not writable</p>
            <div class="solution">
                <strong>🔧 Solution:</strong> Set logs folder permissions to 755 or 777
            </div>
        <?php endif; ?>
        
        <h2>5. Core Files Check</h2>
        <?php
        $coreFiles = [
            'index.php' => 'Main interface',
            'test_connection.php' => 'Connection tester', 
            'migrate.php' => 'Migration handler',
            'src/ImapConnector.php' => 'IMAP connector class',
            'config/config.php' => 'Configuration',
            'assets/style.css' => 'Styles'
        ];
        
        foreach ($coreFiles as $file => $description):
            if (file_exists($file)): ?>
                <p class="status-ok">✅ <?= $file ?> (<?= $description ?>)</p>
            <?php else: ?>
                <p class="status-error">❌ <?= $file ?> (<?= $description ?>) - MISSING</p>
            <?php endif;
        endforeach; ?>
        
        <h2>📋 Summary</h2>
        <?php
        $allGood = $vendorExists && extension_loaded('imap') && version_compare($phpVersion, $requiredPhp, '>=');
        
        if ($allGood): ?>
            <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 5px;">
                <h3>🎉 Installation Complete!</h3>
                <p>Your Mail Migration Tool is ready to use.</p>
                <p><a href="index.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🚀 Start Using Mail Migration Tool</a></p>
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;">
                <h3>⚠️ Installation Incomplete</h3>
                <p>Please follow the solutions above to complete the installation.</p>
                <?php if (!$vendorExists): ?>
                    <p><strong>Most Critical:</strong> Install Composer dependencies!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        <small>Current time: <?= date('Y-m-d H:i:s') ?> | PHP Version: <?= PHP_VERSION ?> | Server: <?= $_SERVER['HTTP_HOST'] ?></small>
    </div>
</body>
</html> 