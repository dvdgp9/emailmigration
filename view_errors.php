<!DOCTYPE html>
<html>
<head>
    <title>Error Log Viewer</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .log { background: #f8d7da; padding: 15px; border: 1px solid #dc3545; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .recent { background: #fff3cd; border-color: #ffc107; }
    </style>
</head>
<body>
    <h1>🔥 Error Log Viewer</h1>
    
    <?php
    $errorLogFiles = [
        'error_log',
        'logs/error.log', 
        '/var/log/php_errors.log',
        ini_get('error_log')
    ];
    
    $found = false;
    
    foreach ($errorLogFiles as $logFile) {
        if ($logFile && file_exists($logFile)) {
            $found = true;
            echo "<h2>📄 Error Log: $logFile</h2>";
            echo "<p><strong>Tamaño:</strong> " . filesize($logFile) . " bytes</p>";
            echo "<p><strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($logFile)) . "</p>";
            
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            
            // Mostrar últimas 20 líneas
            echo "<h3>📍 Últimas 20 líneas:</h3>";
            $lastLines = array_slice($lines, -20);
            
            echo '<div class="log">';
            foreach ($lastLines as $line) {
                if (trim($line)) {
                    // Highlight recent errors (últimos 5 minutos)
                    if (preg_match('/\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                        $errorTime = strtotime($matches[1]);
                        $now = time();
                        if (($now - $errorTime) < 300) { // 5 minutos
                            echo '<div class="recent">' . htmlspecialchars($line) . '</div>';
                        } else {
                            echo htmlspecialchars($line) . "\n";
                        }
                    } else {
                        echo htmlspecialchars($line) . "\n";
                    }
                }
            }
            echo '</div>';
            
            // Buscar errores específicos de migrate.php en las últimas líneas
            echo "<h3>🔍 Errores de migrate.php (últimas 10 líneas):</h3>";
            $migrateErrors = array_filter($lastLines, function($line) {
                return strpos($line, 'migrate.php') !== false;
            });
            
            if ($migrateErrors) {
                echo '<div class="log recent">';
                foreach ($migrateErrors as $error) {
                    echo htmlspecialchars($error) . "\n";
                }
                echo '</div>';
            } else {
                echo '<p>No se encontraron errores específicos de migrate.php en las últimas líneas.</p>';
            }
            
            break;
        }
    }
    
    if (!$found) {
        echo '<div class="log">';
        echo "❌ No se encontró ningún archivo de error log.\n";
        echo "Archivos buscados:\n";
        foreach ($errorLogFiles as $logFile) {
            echo "- $logFile\n";
        }
        echo "\nPHP error_log configurado en: " . ini_get('error_log');
        echo '</div>';
    }
    ?>
    
    <hr>
    <h2>🔧 Para diagnosticar el problema:</h2>
    <ol>
        <li><strong>Busca errores con timestamp reciente</strong> (últimos 5 minutos están destacados)</li>
        <li><strong>Busca líneas que mencionen "migrate.php"</strong></li>
        <li><strong>Los errores más comunes:</strong>
            <ul>
                <li><strong>Parse error:</strong> Error de sintaxis</li>
                <li><strong>Fatal error:</strong> Clase no encontrada, función no definida</li>
                <li><strong>RuntimeException:</strong> Problema con Composer/autoloader</li>
            </ul>
        </li>
    </ol>
    
    <p><a href="test_migrate_basic.php">← Volver al test básico</a></p>
    
</body>
</html> 