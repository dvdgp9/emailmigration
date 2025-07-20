<!DOCTYPE html>
<html>
<head>
    <title>Migration Log Viewer</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .log { background: #f5f5f5; padding: 15px; border: 1px solid #ccc; white-space: pre-wrap; max-height: 500px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>📋 Migration Log Viewer</h1>
    
    <?php
    $logFile = 'logs/migration.log';
    
    if (file_exists($logFile)) {
        echo "<p><strong>Log encontrado:</strong> $logFile</p>";
        echo "<p><strong>Tamaño:</strong> " . filesize($logFile) . " bytes</p>";
        echo "<p><strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($logFile)) . "</p>";
        
        echo "<h2>📄 Contenido completo:</h2>";
        echo '<div class="log">';
        echo htmlspecialchars(file_get_contents($logFile));
        echo '</div>';
        
        echo "<h2>📍 Últimas 20 líneas:</h2>";
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $lastLines = array_slice($lines, -20);
        
        echo '<div class="log">';
        foreach ($lastLines as $line) {
            // Highlight DEBUG lines
            if (strpos($line, 'DEBUG:') !== false) {
                echo '<span style="color: blue;">' . htmlspecialchars($line) . '</span>' . "\n";
            } elseif (strpos($line, 'ERROR:') !== false) {
                echo '<span style="color: red;">' . htmlspecialchars($line) . '</span>' . "\n";
            } else {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo '</div>';
        
    } else {
        echo "<p style='color: red;'>❌ Log no encontrado: $logFile</p>";
        echo "<p>El log se crea cuando se ejecuta una migración.</p>";
    }
    ?>
    
    <hr>
    <p><a href="index.php">← Volver a la webapp</a></p>
    
</body>
</html> 