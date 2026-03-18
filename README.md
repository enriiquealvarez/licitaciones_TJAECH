# Portal de Licitaciones - TJAECH

Sistema web institucional desarrollado por el Tribunal de Justicia Administrativa del Estado de Chiapas. Permite la publicación, gestión y consulta ciudadana de Licitaciones, Adjudicaciones e Invitaciones Restringidas.

## Requisitos del Servidor (Hosting Institucional)
- **Servidor Web**: Apache (recomendado) o Nginx (con reescritura de URLs).
- **PHP**: Versión 8.0 o superior recomendada.
- **Base de Datos**: MySQL 5.7+ o MariaDB 10.3+.
- **Extensiones PHP**: `pdo_mysql`, `fileinfo` (para validación de PDFs). Opcional: `intl` para mejor generación de slugs.

## Pasos de Instalación

1. **Subir los archivos:**
   Sube todo el contenido de la carpeta del proyecto a la carpeta pública (`public_html` si es un dominio principal, o en un subdirectorio). Asegúrate de incluir el archivo `.htaccess`.

2. **Base de Datos:**
   - Crea una nueva base de datos MySQL en tu panel de control (ej. cPanel).
   - Importa el archivo `database.sql` provisto en este paquete para crear las tablas y el usuario administrador por defecto.

3. **Configuración de Conexión:**
   Abre el archivo `includes/config.php` y ajusta las constantes:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'tu_usuario_de_bd');
   define('DB_PASS', 'tu_contraseña_de_bd');
   define('DB_NAME', 'tu_nombre_de_bd');
   define('APP_URL', 'https://tudominio.com'); // Sin barra final
   ```

4. **Permisos de Archivos:**
   Asegúrate de que la carpeta `uploads/pdfs/` tenga permisos de escritura (`755` o `775` dependiendo del servidor) para que PHP pueda guardar los archivos. El archivo `uploads/.htaccess` integrado protegerá la ejecución de código en esta carpeta.

## Credenciales de Acceso por Defecto
Una vez instalado, ingresa desde:
`https://tudominio.com/admin/login.php`

- **Correo**: `informatica@tjaech.gob.mx`
- **Contraseña**: `admin123`

> **IMPORTANTE**: Al ingresar por primera vez, es imperativo que un administrador elimine u oculte este usuario, o cambie la contraseña desde la base de datos o desde el gestor de usuarios para mantener la seguridad.

## Mantenimiento y Rutas

- Las URL públicas se generan automáticamente mediante "Slugs" limpios basados en el título de la licitación. (Ej: `misitio.com/licitacion-publica-nacional-001`).
- Los roles de seguridad están implementados. Un usuario **Administrador** puede agregar nuevos usuarios y editores, acceder a la bitácora y modificar sistema. Un usuario **Editor** puede crear y publicar licitaciones, pero no puede acceder a configuraciones de usuarios o borrarlos masivamente.

## Características de Seguridad
- Inyección SQL prevenida vía consultas preparadas PDO en todas las interacciones.
- Sanitización de Entradas en búsquedas y renderizados frontend contra ataques XSS.
- Contraseñas con Hashing Seguro (Bcrypt nativo de PHP).
- Enrutamiento seguro ocultando `includes` y bloqueando scripts maliciosos en `uploads/`.
