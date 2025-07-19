<?php
/**
 * Mail Migration Tool - Main Interface
 * Herramienta de migración de correos IMAP
 */

// Load configuration
$config = require_once 'config/config.php';

// Set timezone and error reporting
date_default_timezone_set('Europe/Madrid');
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set execution time for large migrations
set_time_limit($config['max_execution_time']);

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['app_name'] ?> v<?= $config['app_version'] ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📧 <?= $config['app_name'] ?></h1>
            <p>Migración sencilla de correos entre servidores IMAP</p>
        </div>

        <!-- Main Form -->
        <div class="main-card">
            <form id="migrationForm" method="POST" action="migrate.php">
                
                <!-- Source Server Section -->
                <div class="server-section">
                    <h2 class="section-title">🔑 Servidor de Origen (Desde donde migrar)</h2>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="source_host">Servidor IMAP</label>
                                <input type="text" id="source_host" name="source_host" class="form-control" 
                                       placeholder="mail.ejemplo.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="source_port">Puerto</label>
                                <input type="number" id="source_port" name="source_port" class="form-control" 
                                       value="<?= $config['default_ports']['imaps'] ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="source_username">Usuario/Email</label>
                                <input type="email" id="source_username" name="source_username" class="form-control" 
                                       placeholder="usuario@origen.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="source_password">Contraseña</label>
                                <input type="password" id="source_password" name="source_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="source_ssl" name="source_ssl" checked> 
                            Usar conexión SSL/TLS (recomendado)
                        </label>
                    </div>
                </div>

                <!-- Destination Server Section -->
                <div class="server-section">
                    <h2 class="section-title">🎯 Servidor de Destino (Hacia donde migrar)</h2>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_host">Servidor IMAP</label>
                                <input type="text" id="dest_host" name="dest_host" class="form-control" 
                                       placeholder="mail.destino.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_port">Puerto</label>
                                <input type="number" id="dest_port" name="dest_port" class="form-control" 
                                       value="<?= $config['default_ports']['imaps'] ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_username">Usuario/Email</label>
                                <input type="email" id="dest_username" name="dest_username" class="form-control" 
                                       placeholder="usuario@destino.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_password">Contraseña</label>
                                <input type="password" id="dest_password" name="dest_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="dest_ssl" name="dest_ssl" checked> 
                            Usar conexión SSL/TLS (recomendado)
                        </label>
                    </div>
                </div>

                <!-- Migration Options -->
                <div class="server-section">
                    <h2 class="section-title">⚙️ Opciones de Migración</h2>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="preserve_flags" name="preserve_flags" 
                                   <?= $config['preserve_flags'] ? 'checked' : '' ?>> 
                            Preservar estado de mensajes (leído/no leído)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="preserve_structure" name="preserve_structure" 
                                   <?= $config['preserve_structure'] ? 'checked' : '' ?>> 
                            Preservar estructura de carpetas
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="batch_size">Emails por lote (menor = más seguro)</label>
                        <input type="number" id="batch_size" name="batch_size" class="form-control" 
                               value="<?= $config['batch_size'] ?>" min="1" max="200">
                    </div>
                </div>

                <!-- Actions -->
                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" id="testConnections" class="btn" style="margin-right: 15px;">
                        🔍 Probar Conexiones
                    </button>
                    <button type="submit" class="btn" id="startMigration">
                        🚀 Iniciar Migración
                    </button>
                </div>
            </form>

            <!-- Progress Section (Hidden by default) -->
            <div class="progress-container" id="progressContainer">
                <h3>Progreso de Migración</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="progressText">Iniciando migración...</p>
                <div id="migrationLog"></div>
            </div>
        </div>

        <!-- Status Information -->
        <div class="status-info">
            <strong>ℹ️ Información:</strong><br>
            • Esta herramienta funciona con cualquier servidor IMAP (Gmail, Outlook, Plesk, cPanel, etc.)<br>
            • Se recomienda hacer una prueba con pocas carpetas antes de migración completa<br>
            • Los archivos adjuntos se migrarán completamente<br>
            • Versión: <?= $config['app_version'] ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('migrationForm');
            const testBtn = document.getElementById('testConnections');
            const migrationBtn = document.getElementById('startMigration');
            const progressContainer = document.getElementById('progressContainer');
            
            // Test connections
            testBtn.addEventListener('click', function() {
                testBtn.disabled = true;
                testBtn.innerHTML = '⏳ Probando...';
                
                const formData = new FormData(form);
                formData.append('action', 'test_connections');
                
                fetch('test_connection.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Conexiones exitosas!\n' + data.message);
                        migrationBtn.disabled = false;
                    } else {
                        alert('❌ Error en conexión:\n' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error: ' + error.message);
                })
                .finally(() => {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '🔍 Probar Conexiones';
                });
            });
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('¿Iniciar migración? Este proceso puede tomar tiempo dependiendo de la cantidad de emails.')) {
                    return;
                }
                
                progressContainer.style.display = 'block';
                migrationBtn.disabled = true;
                migrationBtn.innerHTML = '⏳ Migrando...';
                
                // Here we would handle the actual migration
                // For now, just show the progress container
            });
            
            // Auto-adjust port based on SSL checkbox
            document.getElementById('source_ssl').addEventListener('change', function() {
                const portField = document.getElementById('source_port');
                portField.value = this.checked ? <?= $config['default_ports']['imaps'] ?> : <?= $config['default_ports']['imap'] ?>;
            });
            
            document.getElementById('dest_ssl').addEventListener('change', function() {
                const portField = document.getElementById('dest_port');
                portField.value = this.checked ? <?= $config['default_ports']['imaps'] ?> : <?= $config['default_ports']['imap'] ?>;
            });
        });
    </script>
</body>
</html> 