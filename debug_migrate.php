<?php
/**
 * Debug Migrate - Llamada directa a migrate.php para ver qué devuelve
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Migration</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .output { background: #f5f5f5; padding: 15px; border: 1px solid #ccc; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Debug Migration - Paso a Paso</h1>
    
    <h2>1. Parámetros de prueba</h2>
    <p><strong>IMPORTANTE:</strong> Cambia estos datos por credenciales reales para testing:</p>
    <div class="output"><?php
        $testData = [
            'source_host' => 'ebonemx.plesk.trevenque.es',
            'source_port' => '993',
            'source_ssl' => 'on',
            'source_username' => 'testorigen@ebone.es',
            'source_password' => '6Z7h3^h5o',
            
            'dest_host' => 'ebonemx.plesk.trevenque.es',
            'dest_port' => '993',
            'dest_ssl' => 'on',
            'dest_username' => 'test@ebone.es',
            'dest_password' => 'za*4e768D',
            
            'preserve_flags' => 'on',
            'preserve_structure' => 'on',
            'batch_size' => '5'
        ];
        
        print_r($testData);
    ?></div>
    
    <h2>2. Simulando llamada POST a migrate.php</h2>
    <div class="output"><?php
        // Configurar variables POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $testData;
        
        echo "✅ Variables POST configuradas\n";
        echo "✅ REQUEST_METHOD = " . $_SERVER['REQUEST_METHOD'] . "\n";
    ?></div>
    
    <h2>3. Capturando output de migrate.php</h2>
    <div class="output"><?php
        echo "Iniciando captura...\n";
        
        ob_start();
        $startTime = time();
        
        try {
            include 'migrate.php';
            $endTime = time();
            $output = ob_get_clean();
            
            echo "✅ migrate.php ejecutado sin excepción\n";
            echo "⏱️ Tiempo de ejecución: " . ($endTime - $startTime) . " segundos\n";
            echo "📏 Longitud del output: " . strlen($output) . " bytes\n";
            echo "🔍 Primeros 100 caracteres: " . substr($output, 0, 100) . "\n";
            echo "🔍 Últimos 100 caracteres: " . substr($output, -100) . "\n";
            
        } catch (Exception $e) {
            $endTime = time();
            $output = ob_get_clean();
            
            echo "❌ Excepción capturada: " . $e->getMessage() . "\n";
            echo "⏱️ Tiempo hasta excepción: " . ($endTime - $startTime) . " segundos\n";
            echo "📏 Output antes de excepción: " . strlen($output) . " bytes\n";
            
            if ($output) {
                echo "🔍 Output parcial: " . substr($output, 0, 200) . "\n";
            }
        }
    ?></div>
    
    <h2>4. Output RAW completo</h2>
    <div class="output"><?php
        if (isset($output)) {
            echo "=== INICIO DEL OUTPUT RAW ===\n";
            echo $output;
            echo "\n=== FIN DEL OUTPUT RAW ===\n";
        } else {
            echo "❌ No hay output disponible";
        }
    ?></div>
    
    <h2>5. Análisis JSON</h2>
    <div class="output"><?php
        if (isset($output) && $output) {
            $jsonData = json_decode($output, true);
            $jsonError = json_last_error();
            
            if ($jsonError === JSON_ERROR_NONE) {
                echo "✅ JSON VÁLIDO\n";
                echo "success: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
                echo "message: " . (isset($jsonData['message']) ? substr($jsonData['message'], 0, 100) . '...' : 'N/A') . "\n";
            } else {
                echo "❌ JSON INVÁLIDO\n";
                echo "Error: " . json_last_error_msg() . "\n";
                echo "Error code: " . $jsonError . "\n";
            }
        } else {
            echo "❌ No hay output para analizar";
        }
    ?></div>
    
    <h2>6. Log de errores (si existe)</h2>
    <div class="output"><?php
        $logFile = 'logs/migration.log';
        if (file_exists($logFile)) {
            echo "✅ Log encontrado: " . $logFile . "\n";
            echo "📏 Tamaño: " . filesize($logFile) . " bytes\n\n";
            
            echo "=== ÚLTIMAS 10 LÍNEAS DEL LOG ===\n";
            $logLines = file($logFile, FILE_IGNORE_NEW_LINES);
            $lastLines = array_slice($logLines, -10);
            foreach ($lastLines as $line) {
                echo $line . "\n";
            }
        } else {
            echo "❌ No existe archivo de log: " . $logFile;
        }
    ?></div>
    
    <p><strong>📋 Próximos pasos:</strong></p>
    <ol>
        <li>Actualiza las credenciales en este script con datos reales</li>
        <li>Ejecuta este script: <code>debug_migrate.php</code></li>
        <li>Revisa el output paso a paso</li>
        <li>Identifica exactamente dónde falla la migración</li>
    </ol>
</body>
</html> 