# Toyota Dream Car - Copilot Instructions

## Proyecto Overview

Aplicación web de concurso de dibujo infantil Toyota Dream Car (16° edición). Sistema de registro con subida de archivos, validación por reCAPTCHA y notificación por email.

## Arquitectura Principal

### Estructura de Archivos Clave

- `env.php` - Configuración centralizada de base de datos, email y credenciales
- `home.php` - Página principal del concurso (1929 líneas, compleja)
- `registrar.php` - Endpoint de registro con validación reCAPTCHA y PHPMailer
- `controller/` - Scripts AJAX para cargar combos dependientes (departamentos→provincias→distritos)
- `template/` - Componentes reutilizables (menu-ediciones.php, footer-ediciones.php)
- `ediciones/` - Páginas de ediciones anteriores con rutas relativas específicas

### Configuración de Entorno

Usa `putenv()` en `env.php` para inyectar variables:

```php
putenv("SERVER=$server");
putenv("DATABASE=$database");
// Acceso: getenv('SERVER')
```

### Base de Datos

- Conexión: `mysqli_connect($server, $user_db, $password_db, $db_name, $port_db)`
- **SIEMPRE usar prepared statements**: Jamás concatenar variables directamente en SQL
- Patrón común: verificar conexión con `or die("mensaje")`
- Tablas principales: `departamentos`, `provincias`, `distritos` (geo-jerárquicas)

**Ejemplo obligatorio para consultas SQL:**

```php
// CORRECTO - Prepared Statement
$stmt = $conexion->prepare("SELECT * FROM provincia WHERE id_departamento = ?");
$stmt->bind_param("i", $id_departamento);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

// INCORRECTO - Vulnerable a SQL Injection
$sql = "SELECT * FROM provincia WHERE id_departamento = $id_departamento";
```

## Patrones de Desarrollo

### AJAX con jQuery

Patrón estándar para combos dependientes en `js/cargarCombo.js`:

```javascript
jQuery.ajax({
  type: "POST",
  url: "controller/cargar_departamentos.php",
}).done(function(data) {
  jQuery("#departamento").html(data);
});
```

### Validación y Seguridad

- **reCAPTCHA obligatorio**: Validación en registrar.php antes de procesar
- **PHPMailer**: Configurado con SMTP Gmail, credenciales en env.php
- **Session handling**: `session_start()` en archivos de procesamiento
- **Upload seguro**: Validación MIME real, tamaño máximo 5MB, nombres aleatorios
- **Archivos permitidos**: Solo JPG, JPEG, PNG con validación estricta
- **move_uploaded_file()**: Reemplaza copy() para mayor seguridad
- **Protección .htaccess**: Previene ejecución de scripts en uploads
- **Cifrado de datos sensibles**: DNI cifrados con AES-256-GCM
- **Campos eliminados**: Sin fotos DNI, direcciones, fechas nacimiento apoderado, edad, colegios

**Ejemplo de validación de archivos:**

```php
// Validar archivo con MIME real y tamaño
$validacion = validarArchivoImagen($_FILES['archivo']);
if (!$validacion['valido']) {
    echo "Error: " . $validacion['mensaje']; exit;
}

// Generar nombre aleatorio seguro
$nombre_seguro = generarNombreAleatorio($validacion['extension']);

// Usar move_uploaded_file (nunca copy)
if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
    echo "Error al guardar archivo"; exit;
}
```

**Ejemplo de cifrado de DNI:**

```php
// Cifrar DNI antes de guardar
$dni_cifrado = cifrarDNIValidado($dni_usuario);

// Descifrar DNI para reportes autorizados
$dni_legible = descifrarDNI($dni_cifrado_bd);
```

### CSS y Assets

- **Bootstrap Grid**: 4.4.1 para layout responsivo
- **Fuentes custom**: `css/fuentes.css` para tipografías específicas Toyota
- **Tema Toyota**: `css/toyota.css` (2591 líneas) - colores corporativos definidos (.tc-white, .tc-purple, etc.)
- **Modales**: Sistema custom en `css/modal/` para registro y loader

### Rutas y Navegación

- **Ediciones**: Carpeta `ediciones/` con archivos PHP independientes
- **Assets relativos**: Las ediciones usan `../` para acceder a CSS/JS del root
- **Templates**: `template/menu-ediciones.php` usa rutas relativas específicas

## Flujo de Trabajo

### Desarrollo Local

- Entorno XAMPP en Windows (`c:\xampp_8.0.30\htdocs\`)
- Base de datos MySQL remota (173.201.181.154:3306)
- No usar `localhost` - configuración específica en env.php

### Registro de Participantes

1. Validación reCAPTCHA
2. Procesamiento en `registrar.php`
3. Inserción BD + envío email confirmación
4. Redirección a `gracias.php`

### Gestión de Assets

- Images organizadas por categoría: `images/toyota/cat_1/`, `images/concurso_2025/`
- Dropzone.js para upload de archivos
- Viewer.js para preview de imágenes

## Convenciones Específicas

### Nombres de Archivos

- Ediciones: `edicion-{numero}.php` en carpeta `ediciones/`
- Controllers: `cargar_{entidad}.php` para endpoints AJAX
- CSS: `{funcionalidad}.css` o carpetas temáticas

### HTML Structure

- Clase raíz: `scheme_original` para tema principal
- Google Tag Manager integrado en todas las páginas
- Meta tags específicos Toyota Dream Car

### JavaScript

- jQuery como dependencia principal
- SweetAlert2 para notificaciones
- Axios para algunas peticiones HTTP alternativas

## Debugging y Testing

- Logs de error en procesamiento PHP con `or die()`
- Console.log para debugging AJAX en `cargarCombo.js`
- Variables de entorno para switch entre desarrollo/producción

## Notas Importantes

- **NO hardcodear credenciales** - usar siempre `getenv()`
- **Paths relativos críticos** en carpeta `ediciones/`
- **Validación reCAPTCHA obligatoria** en todos los formularios
- **Responsive design** basado en Bootstrap Grid 4.4.1
