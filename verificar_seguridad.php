<?php
/**
 * Script de verificación del sistema de cifrado
 * Toyota Dream Car - Verificación Post-Auditoría
 */

require_once('env.php');
require_once('funciones_cifrado.php');

echo "=== VERIFICACIÓN DEL SISTEMA DE CIFRADO ===\n\n";

// 1. Verificar funciones de cifrado
echo "1. Verificando funciones de cifrado...\n";

$dni_test = "12345678";
echo "DNI de prueba: $dni_test\n";

$cifrado = cifrarDNI($dni_test);
echo "DNI cifrado: $cifrado (Longitud: " . strlen($cifrado) . ")\n";

$descifrado = descifrarDNI($cifrado);
echo "DNI descifrado: $descifrado\n";

if ($dni_test === $descifrado) {
    echo "✓ Cifrado/Descifrado funcionando correctamente\n\n";
} else {
    echo "✗ ERROR: El cifrado/descifrado NO está funcionando\n\n";
    exit;
}

// 2. Verificar estructura de BD
echo "2. Verificando estructura de base de datos...\n";

$server = getenv('SERVER');
$db_name = getenv('DATABASE');
$user_db = getenv('USER_DB');
$password_db = getenv('PASSWORD_DB');
$port_db = getenv('PORT_DB');

$conexion = mysqli_connect($server, $user_db, $password_db, $db_name, $port_db) 
    or die("Error: No se pudo conectar a la base de datos");

$sql_estructura = "DESCRIBE dreamcar_registros";
$resultado = $conexion->query($sql_estructura);

$campos_dni = [];
while ($row = $resultado->fetch_assoc()) {
    if (strpos($row['Field'], 'dni') !== false) {
        $campos_dni[] = $row;
        echo "Campo: {$row['Field']} | Tipo: {$row['Type']} | Null: {$row['Null']}\n";
    }
}

// Verificar que los campos DNI tengan suficiente espacio
$ok_dni = false;
$ok_apoderado = false;

foreach ($campos_dni as $campo) {
    if ($campo['Field'] === 'dc_dni' && strpos($campo['Type'], 'varchar(100)') !== false) {
        $ok_dni = true;
    }
    if ($campo['Field'] === 'dc_apoderado_dni' && strpos($campo['Type'], 'varchar(100)') !== false) {
        $ok_apoderado = true;
    }
}

echo "\n";
if ($ok_dni) {
    echo "✓ Campo dc_dni tiene suficiente espacio (VARCHAR(100))\n";
} else {
    echo "✗ Campo dc_dni necesita ser ampliado a VARCHAR(100)\n";
}

if ($ok_apoderado) {
    echo "✓ Campo dc_apoderado_dni tiene suficiente espacio (VARCHAR(100))\n";
} else {
    echo "✗ Campo dc_apoderado_dni necesita ser ampliado a VARCHAR(100)\n";
}

// 3. Verificar que no hay vulnerabilidades SQL
echo "\n3. Verificando archivos por vulnerabilidades SQL...\n";

$archivos_verificar = [
    'registrar.php' => 'Endpoint de registro principal',
    'controller/cargar_provincias.php' => 'Carga de provincias',
    'controller/cargar_distritos.php' => 'Carga de distritos'
];

foreach ($archivos_verificar as $archivo => $descripcion) {
    $ruta = "c:\\xampp_8.0.30\\htdocs\\concursodreamcar\\$archivo";
    if (file_exists($ruta)) {
        $contenido = file_get_contents($ruta);
        
        // Buscar concatenaciones peligrosas
        if (preg_match('/WHERE.*\$.*[^?]/', $contenido) || 
            preg_match('/SELECT.*\$[^,]*[^?]/', $contenido)) {
            echo "⚠️  $descripcion: Posible concatenación SQL detectada\n";
        } else {
            echo "✓ $descripcion: Sin concatenaciones SQL detectadas\n";
        }
    } else {
        echo "⚠️  $descripcion: Archivo no encontrado\n";
    }
}

// 4. Verificar archivos de seguridad
echo "\n4. Verificando archivos de seguridad...\n";

$archivos_seguridad = [
    'funciones_cifrado.php' => 'Funciones de cifrado',
    '.htaccess' => 'Protección de directorios',
    'config_s3.php' => 'Configuración S3'
];

foreach ($archivos_seguridad as $archivo => $descripcion) {
    $ruta = "c:\\xampp_8.0.30\\htdocs\\concursodreamcar\\$archivo";
    if (file_exists($ruta)) {
        echo "✓ $descripcion: Presente\n";
    } else {
        echo "⚠️  $descripcion: No encontrado\n";
    }
}

echo "\n=== RESUMEN DE VERIFICACIÓN ===\n";
echo "Sistema de cifrado: " . ($dni_test === $descifrado ? "✓ OK" : "✗ ERROR") . "\n";
echo "Estructura BD: " . ($ok_dni && $ok_apoderado ? "✓ OK" : "⚠️  REQUIERE ACTUALIZACIÓN") . "\n";
echo "Protección SQL: Revisar manualmente los archivos marcados\n";
echo "Archivos seguridad: Revisar archivos faltantes\n";

$conexion->close();
?>