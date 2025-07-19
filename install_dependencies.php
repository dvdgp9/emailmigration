<?php
/**
 * Auto-installer for Composer dependencies
 * Instalador automático de dependencias de Composer
 */

set_time_limit(300); // 5 minutes timeout

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Install Dependencies</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .log { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .progress { background: #e9ecef; border-radius: 10px; padding: 3px; margin: 15px 0; }
        .progress-bar { background: #007bff; height: 20px; border-radius: 7px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Auto-Install Dependencies</h1>
        
        <?php if (!isset($_POST['install'])): ?>
            <p>This will automatically install the required Composer dependencies for the Mail Migration Tool.</p>
            <p><strong>What this does:</strong></p>
            <ul>
                <li>Downloads Composer (PHP package manager)</li>
                <li>Installs ddeboer/imap library</li>
                <li>Sets up autoloader</li>
            </ul>
            
            <form method="post">
                <button type="submit" name="install" style="background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                    🚀 Install Dependencies Now
                </button>
            </form>
            
        <?php else: ?>
            
            <div class="progress">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="log" id="log">Starting installation...\n</div>
            
            <script>
                function updateProgress(percent, message) {
                    document.getElementById('progressBar').style.width = percent + '%';
                    document.getElementById('log').innerHTML += message + '\n';
                    document.getElementById('log').scrollTop = document.getElementById('log').scrollHeight;
                }
            </script>
            
            <?php
            
            function logMessage($message) {
                echo "<script>updateProgress(" . ($GLOBALS['progress'] ?? 0) . ", '" . addslashes($message) . "');</script>";
                flush();
                ob_flush();
            }
            
            try {
                $progress = 10;
                logMessage("🔍 Checking current status...");
                
                // Check if already installed
                if (file_exists('vendor/autoload.php')) {
                    $progress = 100;
                    logMessage("✅ Dependencies already installed!");
                    logMessage("📁 vendor/autoload.php exists");
                    echo "<script>updateProgress(100, '✅ Installation already complete!');</script>";
                    echo '<p class="success">✅ Dependencies are already installed!</p>';
                    echo '<p><a href="check_installation.php">← Back to Installation Check</a></p>';
                } else {
                    
                    $progress = 20;
                    logMessage("📥 Downloading Composer...");
                    
                    // Download composer.phar
                    $composerUrl = 'https://getcomposer.org/download/latest-stable/composer.phar';
                    $composerContent = file_get_contents($composerUrl);
                    
                    if ($composerContent === false) {
                        throw new Exception("Failed to download Composer");
                    }
                    
                    file_put_contents('composer.phar', $composerContent);
                    chmod('composer.phar', 0755);
                    
                    $progress = 40;
                    logMessage("✅ Composer downloaded successfully");
                    
                    // Check if composer.json exists
                    if (!file_exists('composer.json')) {
                        $progress = 50;
                        logMessage("📝 Creating composer.json...");
                        
                        $composerJson = [
                            "name" => "mail-migration/webapp",
                            "description" => "IMAP Email Migration Tool",
                            "type" => "project",
                            "license" => "MIT",
                            "require" => [
                                "php" => ">=7.4",
                                "ddeboer/imap" => "^1.21"
                            ],
                            "autoload" => [
                                "psr-4" => [
                                    "MailMigration\\" => "src/"
                                ]
                            ]
                        ];
                        
                        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
                        logMessage("✅ composer.json created");
                    }
                    
                    $progress = 60;
                    logMessage("🔧 Installing dependencies...");
                    logMessage("⏳ This may take a few minutes...");
                    
                    // Run composer install
                    $output = [];
                    $returnVar = 0;
                    
                    // Try different PHP executables
                    $phpExecutables = ['php', 'php8.1', 'php8.0', 'php7.4', '/usr/bin/php'];
                    $composerInstalled = false;
                    
                    foreach ($phpExecutables as $php) {
                        $command = "$php composer.phar install --no-dev --optimize-autoloader 2>&1";
                        logMessage("🔄 Trying: $command");
                        
                        exec($command, $output, $returnVar);
                        
                        if ($returnVar === 0 && file_exists('vendor/autoload.php')) {
                            $composerInstalled = true;
                            break;
                        }
                        
                        // Clear output for next attempt
                        $output = [];
                    }
                    
                    if (!$composerInstalled) {
                        // Try alternative method - direct download
                        $progress = 70;
                        logMessage("⚠️ Composer install failed, trying alternative method...");
                        
                        // Create vendor directory structure manually
                        $vendorDirs = ['vendor', 'vendor/composer', 'vendor/ddeboer', 'vendor/ddeboer/imap'];
                        foreach ($vendorDirs as $dir) {
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                        }
                        
                        // Download ddeboer/imap directly from GitHub
                        $progress = 80;
                        logMessage("📥 Downloading ddeboer/imap directly...");
                        
                        $zipUrl = 'https://github.com/ddeboer/imap/archive/refs/tags/1.21.0.zip';
                        $zipContent = file_get_contents($zipUrl);
                        
                        if ($zipContent !== false) {
                            file_put_contents('imap-temp.zip', $zipContent);
                            
                            // Extract zip (if ZipArchive is available)
                            if (class_exists('ZipArchive')) {
                                $zip = new ZipArchive;
                                if ($zip->open('imap-temp.zip') === TRUE) {
                                    $zip->extractTo('temp-extract/');
                                    $zip->close();
                                    
                                    // Move files to vendor directory
                                    if (is_dir('temp-extract/imap-1.21.0/src')) {
                                        rename('temp-extract/imap-1.21.0/src', 'vendor/ddeboer/imap/src');
                                    }
                                    
                                    // Clean up
                                    unlink('imap-temp.zip');
                                    $this->rrmdir('temp-extract');
                                    
                                    // Create basic autoloader
                                    $autoloaderContent = '<?php
// Simple autoloader for Mail Migration Tool
spl_autoload_register(function ($class) {
    $prefix = "MailMigration\\\\";
    $base_dir = __DIR__ . "/../src/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load ddeboer/imap classes
spl_autoload_register(function ($class) {
    $prefix = "Ddeboer\\\\Imap\\\\";
    $base_dir = __DIR__ . "/../ddeboer/imap/src/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});
?>';
                                    file_put_contents('vendor/autoload.php', $autoloaderContent);
                                    $composerInstalled = true;
                                }
                            }
                        }
                    }
                    
                    $progress = 90;
                    if ($composerInstalled && file_exists('vendor/autoload.php')) {
                        logMessage("✅ Dependencies installed successfully!");
                        
                        // Clean up composer.phar
                        if (file_exists('composer.phar')) {
                            unlink('composer.phar');
                        }
                        
                        $progress = 100;
                        logMessage("🎉 Installation complete!");
                        echo "<script>updateProgress(100, '🎉 Installation complete!');</script>";
                        echo '<div class="success">✅ Dependencies installed successfully!</div>';
                        echo '<p><a href="check_installation.php">← Back to Installation Check</a></p>';
                        echo '<p><a href="index.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🚀 Start Using Mail Migration Tool</a></p>';
                    } else {
                        throw new Exception("Failed to install dependencies. Please contact your hosting provider or install manually.");
                    }
                }
                
            } catch (Exception $e) {
                logMessage("❌ Error: " . $e->getMessage());
                echo '<div class="error">❌ Installation failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<p>Please try manual installation or contact your hosting provider.</p>';
                echo '<p><a href="check_installation.php">← Back to Installation Check</a></p>';
            }
            
            ?>
            
        <?php endif; ?>
    </div>
</body>
</html> 