# 📧 Mail Migration Tool

Herramienta web para migrar correos entre servidores IMAP de manera sencilla y eficiente.

## 🚀 Características

- **Universal**: Funciona con cualquier servidor IMAP (Gmail, Outlook, Plesk, cPanel, etc.)
- **Interfaz intuitiva**: Webapp fácil de usar con formularios simples
- **Migración completa**: Incluye attachments, flags y estructura de carpetas
- **Progreso en tiempo real**: Seguimiento visual del proceso de migración
- **Configurable**: Opciones personalizables para diferentes necesidades
- **Optimizada para cPanel**: Perfecta para hosting compartido

## 📋 Requisitos

- PHP 7.4 o superior
- Extensión PHP IMAP habilitada
- Composer (para dependencias)
- Servidor web (Apache/Nginx)

## 🔧 Instalación

### Opción 1: Instalación Automática (Recomendada)

1. **Descargar y subir archivos** a tu servidor web
2. **Ejecutar verificador de instalación:**
   ```bash
   php install.php
   ```
3. **Seguir las instrucciones** mostradas por el instalador

### Opción 2: Instalación Manual

1. **Clonar o descargar el proyecto:**
   ```bash
   git clone [repository-url] mail-migration
   cd mail-migration
   ```

2. **Verificar requisitos del sistema:**
   - PHP 7.4 o superior
   - Extensión PHP IMAP habilitada
   - Permisos de escritura en directorio logs/

3. **Instalar dependencias:**
   ```bash
   composer install
   ```
   
   Si no tienes Composer instalado:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install
   ```

4. **Configurar permisos:**
   ```bash
   chmod 755 logs/
   chmod 644 config/config.php
   ```

5. **Subir a tu servidor** (cPanel, FTP, etc.)

### Para cPanel / Hosting Compartido

1. **Subir archivos** via File Manager o FTP
2. **Activar extensión IMAP** en "Select PHP Extensions"
3. **Usar terminal en cPanel** para ejecutar `php install.php`
4. **Instalar dependencias** con `composer install`

## 🎯 Uso

1. Acceder a `index.php` desde tu navegador
2. Completar los datos del **servidor de origen**:
   - Servidor IMAP
   - Puerto (993 para SSL, 143 para no SSL)
   - Usuario y contraseña
3. Completar los datos del **servidor de destino**
4. Configurar opciones de migración
5. Hacer clic en **"Probar Conexiones"** para verificar
6. Hacer clic en **"Iniciar Migración"**

## 📁 Estructura del Proyecto

```
mail-migration/
├── assets/                 # CSS y archivos estáticos
│   └── style.css          # Estilos de la interfaz web
├── config/                 # Archivos de configuración
│   └── config.php         # Configuración principal
├── logs/                   # Logs de migración
├── src/                    # Clases PHP
│   └── ImapConnector.php  # Clase principal para conexiones IMAP
├── vendor/                 # Dependencias de Composer (después de install)
├── .gitignore             # Configuración de Git
├── composer.json           # Configuración de dependencias
├── index.php              # Interfaz principal
├── install.php            # Verificador de instalación
├── migrate.php            # Manejador de migración
├── test_connection.php    # Prueba de conexiones IMAP
└── README.md              # Este archivo
```

## ⚙️ Configuración

Editar `config/config.php` para personalizar:

- Timeouts de conexión
- Tamaño de lotes para migración
- Configuraciones de logging
- Puertos por defecto

## 🔒 Seguridad

- Las credenciales se manejan solo en memoria durante la migración
- Conexiones SSL/TLS habilitadas por defecto
- No se almacenan passwords en archivos

## 🐛 Troubleshooting

**Error de conexión IMAP:**
- Verificar que la extensión PHP IMAP esté instalada
- Confirmar puertos y SSL del servidor
- Revisar logs en `logs/migration.log`

**Timeouts:**
- Reducir el tamaño del lote en configuración
- Verificar límites de ejecución del servidor

## 📄 Licencia

MIT License - Ver archivo LICENSE

## 🤝 Soporte

Para soporte, crear un issue en el repositorio o contactar al desarrollador.

---

**Versión**: 1.0.0  
**Estado**: En desarrollo activo 