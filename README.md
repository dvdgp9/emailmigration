# 📦 Migrabox

Herramienta web profesional para migrar correos entre servidores IMAP de manera sencilla, eficiente y con estilo moderno.

## 🚀 Características

- **🌐 Universal**: Compatible con cualquier servidor IMAP (Gmail, Outlook, Plesk, cPanel, etc.)
- **✨ Interfaz Moderna**: Diseño profesional estilo Airbnb con UX optimizada
- **📧 Migración Completa**: Incluye attachments, flags, estructura de carpetas y metadatos
- **⚡ Progreso en Tiempo Real**: Seguimiento visual con barra de progreso avanzada
- **🔧 Altamente Configurable**: Opciones de batch, timeouts y filtros personalizables
- **🏗️ Optimizada para cPanel**: Perfecta para hosting compartido y servidores dedicados
- **🔄 Procesamiento por Lotes**: Manejo eficiente de grandes volúmenes de correo
- **🛡️ Preservación de Flags**: Mantiene estados de lectura, flags personalizados y más

## 📋 Requisitos

- **PHP 8.0+** (recomendado 8.3+)
- **Extensión PHP IMAP** habilitada
- **Composer** (para gestión de dependencias)
- **Servidor web** (Apache/Nginx)
- **Memoria**: Mínimo 128MB, recomendado 256MB+ para grandes migraciones

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
   git clone [repository-url] migrabox
   cd migrabox
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

1. **Acceder** a `index.php` desde tu navegador
2. **Completar datos del servidor de origen**:
   - Servidor IMAP (ej: imap.gmail.com)
   - Puerto (993 para SSL, 143 para no SSL)
   - Usuario/Email y contraseña
3. **Completar datos del servidor de destino**
4. **Configurar opciones avanzadas**:
   - Tamaño de lote (recomendado: 50-100)
   - Preservar flags y estructura
   - Timeout personalizado
5. **Probar conexiones** antes de migrar
6. **Iniciar migración** y seguir progreso en tiempo real

### 🎨 **Nueva Interfaz Airbnb-Style**
- **Diseño Moderno**: Inspirado en los mejores estándares de UI/UX
- **Responsive**: Optimizado para desktop y móvil
- **Intuitive**: Flujo de trabajo simplificado y claro
- **Progress Tracking**: Barra de progreso visual con detalles en tiempo real

## 📁 Estructura del Proyecto

```
migrabox/
├── assets/                 # CSS y archivos estáticos  
│   └── style.css          # Estilos modernos estilo Airbnb
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

- **Timeouts de conexión**: Ajustar según velocidad del servidor
- **Tamaño de lotes**: Optimizar para volumen de emails (default: 50)
- **Configuraciones de logging**: Nivel de detalle en logs
- **Puertos por defecto**: SSL (993) vs No-SSL (143)
- **Memoria límites**: Para migraciones de gran volumen

### 🔧 **Configuraciones Recomendadas**

**Para Migraciones Pequeñas (< 1000 emails):**
- Batch size: 100
- Timeout: 30s
- Memory limit: 128MB

**Para Migraciones Medianas (1K-10K emails):**
- Batch size: 50
- Timeout: 60s  
- Memory limit: 256MB

**Para Migraciones Grandes (> 10K emails):**
- Batch size: 25-50
- Timeout: 120s
- Memory limit: 512MB+

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

**Versión**: 2.0.0  
**Estado**: ✅ Producción estable con UI moderna  
**Último Update**: Diseño Airbnb-style, procesamiento por lotes optimizado 