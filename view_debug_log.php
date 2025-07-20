<?php
/**
 * Debug Log Viewer - Para revisar problemas con flags
 */
$logFile = 'logs/migration.log';

if (!file_exists($logFile)) {
    echo "<h2>❌ No hay logs disponibles</h2>";
    echo "<p>El archivo $logFile no existe.</p>";
    exit;
}

$logs = file_get_contents($logFile);
$lines = explode("\n", $logs);

// Filtrar solo logs de debug de flags
$flagLogs = array_filter($lines, function($line) {
    return strpos($line, 'DEBUG: Message') !== false || strpos($line, 'DEBUG: Flags to apply') !== false;
});

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔍 Debug Log - Flag Preservation</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .log-line { margin: 5px 0; padding: 5px; background: #f9f9f9; border-left: 3px solid #007cba; }
        .debug-seen { background: #e8f5e8; border-left-color: #28a745; }
        .debug-unseen { background: #fff3cd; border-left-color: #ffc107; }
        .debug-flags { background: #e1ecf4; border-left-color: #007cba; }
        h1 { color: #333; }
        .stats { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .clear-btn { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .clear-btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Log - Flag Preservation Analysis</h1>
        
        <div class="stats">
            <strong>📊 Estadísticas del Log:</strong><br>
            • Total líneas de debug de flags: <?= count($flagLogs) ?><br>
            • Archivo: <?= $logFile ?><br>
            • Tamaño: <?= round(filesize($logFile) / 1024, 2) ?> KB<br>
            • Última modificación: <?= date('Y-m-d H:i:s', filemtime($logFile)) ?>
        </div>
        
        <button class="clear-btn" onclick="if(confirm('¿Borrar todos los logs?')) { window.location.href='?clear=1'; }">
            🗑️ Limpiar Logs
        </button>
        
        <h2>🚩 Logs de Detección de Flags:</h2>
        
        <?php if (empty($flagLogs)): ?>
            <p><em>No hay logs de debug de flags disponibles. Ejecuta una migración con preserve_flags=true para generar logs.</em></p>
        <?php else: ?>
            <?php foreach ($flagLogs as $line): ?>
                <?php if (empty(trim($line))) continue; ?>
                <?php 
                $class = 'log-line';
                if (strpos($line, 'Seen: YES') !== false) {
                    $class .= ' debug-seen';
                } elseif (strpos($line, 'Seen: NO') !== false) {
                    $class .= ' debug-unseen';
                } elseif (strpos($line, 'Flags to apply') !== false) {
                    $class .= ' debug-flags';
                }
                ?>
                <div class="<?= $class ?>"><?= htmlspecialchars($line) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" style="color: #007cba; text-decoration: none;">⬅️ Volver a Migración</a> |
            <a href="?refresh=1" style="color: #007cba; text-decoration: none;">🔄 Refrescar</a>
        </div>
    </div>
</body>
</html>

<?php
// Handle clear logs
if (isset($_GET['clear'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        echo "<script>alert('Logs limpiados'); window.location.href='" . strtok($_SERVER["REQUEST_URI"], '?') . "';</script>";
    }
}
?> 