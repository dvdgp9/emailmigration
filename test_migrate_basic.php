<!DOCTYPE html>
<html>
<head>
    <title>Test Migrate Basic</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .result { background: #f5f5f5; padding: 15px; border: 1px solid #ccc; white-space: pre-wrap; margin: 10px 0; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
    </style>
</head>
<body>
    <h1>🧪 Test Basic - ¿Se ejecuta migrate.php?</h1>
    
    <p>Este test hace exactamente la misma llamada AJAX que la interfaz web.</p>
    
    <button onclick="testMigration()">🚀 Probar migrate.php</button>
    
    <div id="results"></div>
    
    <script>
    function testMigration() {
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = '<div class="result">⏳ Probando migrate.php...</div>';
        
        // Crear FormData exactamente como lo hace index.php
        const formData = new FormData();
        formData.append('source_host', 'ebonemx.plesk.trevenque.es');
        formData.append('source_port', '993');
        formData.append('source_ssl', 'on');
        formData.append('source_username', 'testorigen@ebone.es');
        formData.append('source_password', '6Z7h3^h5o');
        formData.append('dest_host', 'ebonemx.plesk.trevenque.es');
        formData.append('dest_port', '993');
        formData.append('dest_ssl', 'on');  
        formData.append('dest_username', 'test@ebone.es');
        formData.append('dest_password', 'za*4e768D');
        formData.append('preserve_flags', 'on');
        formData.append('preserve_structure', 'on');
        formData.append('batch_size', '5');
        
        const startTime = Date.now();
        
        // Hacer fetch exactamente como index.php
        fetch('migrate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const endTime = Date.now();
            const duration = endTime - startTime;
            
            resultsDiv.innerHTML += `<div class="result">⏱️ Tiempo de respuesta: ${duration}ms</div>`;
            resultsDiv.innerHTML += `<div class="result">📡 Status HTTP: ${response.status} ${response.statusText}</div>`;
            resultsDiv.innerHTML += `<div class="result">📤 Content-Type: ${response.headers.get('content-type')}</div>`;
            
            // Intentar obtener texto de la respuesta
            return response.text();
        })
        .then(responseText => {
            resultsDiv.innerHTML += `<div class="result">📏 Longitud respuesta: ${responseText.length} bytes</div>`;
            
            if (responseText.length > 0) {
                resultsDiv.innerHTML += `<div class="result success">✅ migrate.php SÍ responde</div>`;
                resultsDiv.innerHTML += `<div class="result">📄 Respuesta RAW (primeros 500 chars):<br>${responseText.substring(0, 500).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`;
                
                // Intentar parsear JSON
                try {
                    const jsonData = JSON.parse(responseText);
                    resultsDiv.innerHTML += `<div class="result success">✅ JSON VÁLIDO</div>`;
                    resultsDiv.innerHTML += `<div class="result">success: ${jsonData.success}</div>`;
                    resultsDiv.innerHTML += `<div class="result">message: ${jsonData.message || 'N/A'}</div>`;
                } catch (e) {
                    resultsDiv.innerHTML += `<div class="result error">❌ JSON INVÁLIDO: ${e.message}</div>`;
                }
            } else {
                resultsDiv.innerHTML += `<div class="result error">❌ migrate.php devuelve respuesta VACÍA</div>`;
            }
        })
        .catch(error => {
            const endTime = Date.now();
            const duration = endTime - startTime;
            
            resultsDiv.innerHTML += `<div class="result error">❌ ERROR de red/timeout después de ${duration}ms</div>`;
            resultsDiv.innerHTML += `<div class="result error">Error: ${error.message}</div>`;
        })
        .finally(() => {
            // Mostrar si hay log después del intento
            checkForLog();
        });
    }
    
    function checkForLog() {
        fetch('view_log.php')
        .then(response => response.text())
        .then(html => {
            if (html.includes('Log encontrado')) {
                document.getElementById('results').innerHTML += `<div class="result success">📋 ¡Log de migración SE CREÓ! <a href="view_log.php" target="_blank">Ver log</a></div>`;
            } else {
                document.getElementById('results').innerHTML += `<div class="result error">📋 Log de migración NO se creó</div>`;
            }
        })
        .catch(e => {
            document.getElementById('results').innerHTML += `<div class="result error">❌ Error verificando log: ${e.message}</div>`;
        });
    }
    </script>
    
    <hr>
    <h2>📋 ¿Qué nos dirá este test?</h2>
    <ul>
        <li><strong>Tiempo de respuesta:</strong> Si es >30s, hay timeout</li>
        <li><strong>Status HTTP:</strong> 200=OK, 500=error servidor, etc.</li>
        <li><strong>Respuesta vacía:</strong> migrate.php falla antes de devolver nada</li>
        <li><strong>JSON inválido:</strong> migrate.php devuelve error HTML</li>
        <li><strong>Log creado:</strong> Si llega al primer comando de logging</li>
    </ul>
    
</body>
</html> 