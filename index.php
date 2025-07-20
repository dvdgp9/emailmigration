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
    <!-- Heroicons for modern icons -->
    <script src="https://unpkg.com/@heroicons/react@2.0.18/24/outline/index.js" type="module"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>📧 <?= $config['app_name'] ?></h1>
            <p>Migración profesional de correos entre servidores IMAP con preservación completa de estructura y flags</p>
        </header>

        <!-- Main Form -->
        <div class="main-card">
            <form id="migrationForm" method="POST" action="migrate.php">
                
                <!-- Source Server Section -->
                <section class="server-section">
                    <h2 class="section-title">
                        🔑 Servidor de Origen
                        <span style="font-weight: var(--font-normal); font-size: var(--text-sm); color: var(--gray-500);">Desde donde migrar</span>
                    </h2>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="source_host">Servidor IMAP</label>
                                <input type="text" id="source_host" name="source_host" class="form-control" 
                                       value="ebonemx.plesk.trevenque.es" placeholder="mail.ejemplo.com" required>
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
                                       value="testorigen@ebone.es" placeholder="usuario@origen.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="source_password">Contraseña</label>
                                <input type="password" id="source_password" name="source_password" class="form-control" 
                                       value="6Z7h3^h5o" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="source_ssl" name="source_ssl" checked> 
                            Usar conexión SSL/TLS (recomendado)
                        </label>
                    </div>
                </section>

                <!-- Destination Server Section -->
                <section class="server-section">
                    <h2 class="section-title">
                        🎯 Servidor de Destino
                        <span style="font-weight: var(--font-normal); font-size: var(--text-sm); color: var(--gray-500);">Hacia donde migrar</span>
                    </h2>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_host">Servidor IMAP</label>
                                <input type="text" id="dest_host" name="dest_host" class="form-control" 
                                       value="ebonemx.plesk.trevenque.es" placeholder="mail.destino.com" required>
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
                                       value="test@ebone.es" placeholder="usuario@destino.com" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="dest_password">Contraseña</label>
                                <input type="password" id="dest_password" name="dest_password" class="form-control" 
                                       value="za*4e768D" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="dest_ssl" name="dest_ssl" checked> 
                            Usar conexión SSL/TLS (recomendado)
                        </label>
                    </div>
                </section>

                <!-- Migration Options -->
                <section class="server-section">
                    <h2 class="section-title">
                        ⚙️ Opciones de Migración
                    </h2>
                    
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
                        <label for="batch_size">Emails por lote (batch size)</label>
                        <input type="number" id="batch_size" name="batch_size" class="form-control" 
                               value="50" min="1" max="200">
                        <small>
                            Procesa emails en lotes de este tamaño con pausas entre lotes.<br>
                            Ejemplo: 500 emails con batch=50 → 10 lotes de 50 emails cada uno.<br>
                            <strong>Menor valor = más seguro</strong> (especialmente para emails con adjuntos grandes)
                        </small>
                    </div>
                </section>

                <!-- Actions -->
                <div class="text-center mt-4">
                    <div style="display: flex; gap: var(--space-3); justify-content: center; flex-wrap: wrap;">
                        <button type="button" id="testConnections" class="btn-secondary btn">
                            🔍 Probar Conexiones
                        </button>
                        <button type="submit" class="btn-primary btn" id="startMigration">
                            🚀 Iniciar Migración
                        </button>
                    </div>
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
        <aside class="status-info">
            <div style="display: flex; align-items: flex-start; gap: var(--space-2);">
                <span style="font-size: var(--text-lg);">ℹ️</span>
                <div>
                    <strong>Información de la herramienta:</strong>
                    <ul style="margin: var(--space-1) 0 0 0; padding-left: var(--space-4); color: var(--gray-600);">
                        <li>✅ Compatible con cualquier servidor IMAP (Gmail, Outlook, Plesk, cPanel)</li>
                        <li>🗂️ Migración completa con preservación de carpetas y flags</li>
                        <li>📎 Incluye archivos adjuntos de cualquier tamaño</li>
                        <li>🔄 Procesamiento por lotes para máxima estabilidad</li>
                    </ul>
                    <p style="margin-top: var(--space-2); font-size: var(--text-xs); color: var(--gray-400);">
                        Versión: <?= $config['app_version'] ?> | Sistema production-ready
                    </p>
                </div>
            </div>
        </aside>
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
                
                // Show progress container and disable form
                progressContainer.style.display = 'block';
                migrationBtn.disabled = true;
                migrationBtn.innerHTML = '⏳ Migrando...';
                testBtn.disabled = true;
                
                // Reset progress
                const progressFill = document.getElementById('progressFill');
                const progressText = document.getElementById('progressText');
                const migrationLog = document.getElementById('migrationLog');
                
                progressFill.style.width = '0%';
                progressText.textContent = 'Iniciando migración...';
                migrationLog.innerHTML = '';
                
                // Prepare form data
                const formData = new FormData(form);
                
                // Start migration
                fetch('migrate.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Migration completed successfully
                        progressFill.style.width = '100%';
                        progressText.textContent = '✅ ¡Migración completada exitosamente!';
                        migrationLog.innerHTML = `
                            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 10px;">
                                <strong>✅ Migración exitosa</strong><br>
                                ${data.message.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        
                        // Show detailed results if available
                        if (data.details) {
                            migrationLog.innerHTML += `
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; font-size: 0.9em;">
                                    <strong>📊 Detalles de migración:</strong><br>
                                    • Total de carpetas: ${data.details.total_mailboxes}<br>
                                    • Carpetas procesadas: ${data.details.processed_mailboxes}<br>
                                    • Mensajes totales: ${data.details.total_messages}<br>
                                    • Mensajes migrados: ${data.details.migrated_messages}<br>
                                    • Errores: ${data.details.error_count}
                                </div>
                            `;
                        }
                        
                    } else {
                        // Migration failed or completed with errors
                        progressFill.style.width = '100%';
                        progressFill.style.background = '#dc3545';
                        progressText.textContent = '❌ Error en migración';
                        migrationLog.innerHTML = `
                            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 10px;">
                                <strong>❌ Error en migración</strong><br>
                                ${data.message.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        
                        // Show detailed error results if available
                        if (data.details) {
                            migrationLog.innerHTML += `
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; font-size: 0.9em;">
                                    <strong>📊 Resumen de intentos:</strong><br>
                                    • Total de carpetas: ${data.details.total_mailboxes}<br>
                                    • Carpetas exitosas: ${data.details.successful_mailboxes}<br>
                                    • Carpetas fallidas: ${data.details.failed_mailboxes}<br>
                                    • Mensajes migrados: ${data.details.migrated_messages}<br>
                                    • Errores totales: ${data.details.error_count}
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    // Network or parsing error
                    progressFill.style.width = '100%';
                    progressFill.style.background = '#dc3545';
                    progressText.textContent = '❌ Error de conexión';
                    migrationLog.innerHTML = `
                        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <strong>❌ Error de conexión</strong><br>
                            ${error.message}
                        </div>
                    `;
                })
                .finally(() => {
                    // Re-enable buttons
                    migrationBtn.disabled = false;
                    migrationBtn.innerHTML = '🚀 Iniciar Migración';
                    testBtn.disabled = false;
                });
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