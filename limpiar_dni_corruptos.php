<?php
/**
 * Script para limpiar y re-cifrar DNI corruptos
 * Toyota Dream Car - Corrección de Auditoría de Seguridad
 * 
 * IMPORTANTE: Ejecutar DESPUÉS de actualizar la estructura de BD
 */

require_once('env.php');
require_once('funciones_cifrado.php');

echo "=== LIMPIEZA Y RE-CIFRADO DE DNI CORRUPTOS ===\n\n";

// Conexión a BD
$server = getenv('SERVER');
$db_name = getenv('DATABASE');
$user_db = getenv('USER_DB');
$password_db = getenv('PASSWORD_DB');
$port_db = getenv('PORT_DB');

$conexion = mysqli_connect($server, $user_db, $password_db, $db_name, $port_db) 
    or die("Error: No se pudo conectar a la base de datos");

// 1. Buscar registros con DNI corruptos
echo "1. Buscando registros con DNI corruptos...\n";

$sql_buscar = "SELECT id, codigo, dc_nombre, dc_apellido, dc_dni, dc_apoderado_dni 
               FROM dreamcar_registros 
               WHERE LENGTH(dc_dni) < 20 AND dc_dni REGEXP '[+/=]'";

$resultado = $conexion->query($sql_buscar);
$registros_corruptos = [];

if ($resultado->num_rows > 0) {
    echo "Encontrados " . $resultado->num_rows . " registros con DNI posiblemente corruptos:\n\n";
    
    while ($row = $resultado->fetch_assoc()) {
        $registros_corruptos[] = $row;
        echo "ID: {$row['id']} | Código: {$row['codigo']} | {$row['dc_nombre']} {$row['dc_apellido']}\n";
        echo "  DNI: '{$row['dc_dni']}' (Longitud: " . strlen($row['dc_dni']) . ")\n";
        echo "  DNI Apoderado: '{$row['dc_apoderado_dni']}' (Longitud: " . strlen($row['dc_apoderado_dni']) . ")\n\n";
    }
} else {
    echo "No se encontraron registros con DNI corruptos.\n";
    exit;
}

// 2. Solicitar confirmación
echo "\n¿Desea proceder a MARCAR estos registros como corruptos? (y/n): ";
$confirmar = trim(fgets(STDIN));

if (strtolower($confirmar) !== 'y') {
    echo "Operación cancelada.\n";
    exit;
}

// 3. Marcar registros corruptos (en lugar de intentar descifrarlos)
echo "\n2. Marcando registros corruptos...\n";

$stmt_update = $conexion->prepare("UPDATE dreamcar_registros SET 
                                   dc_dni = '[CORRUPTED_NEEDS_REENTRY]',
                                   dc_apoderado_dni = '[CORRUPTED_NEEDS_REENTRY]'
                                   WHERE id = ?");

$corregidos = 0;
foreach ($registros_corruptos as $registro) {
    $stmt_update->bind_param("i", $registro['id']);
    if ($stmt_update->execute()) {
        echo "✓ Registro ID {$registro['id']} marcado como corrupto\n";
        $corregidos++;
    } else {
        echo "✗ Error al marcar registro ID {$registro['id']}: " . $conexion->error . "\n";
    }
}

$stmt_update->close();

echo "\n=== RESUMEN ===\n";
echo "Registros procesados: " . count($registros_corruptos) . "\n";
echo "Registros marcados como corruptos: $corregidos\n";
echo "\nNOTA: Los registros marcados necesitarán que el usuario vuelva a registrarse\n";
echo "      o que se contacte manualmente para obtener sus DNI reales.\n";

$conexion->close();
?>