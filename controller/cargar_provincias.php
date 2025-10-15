<?php
  
  header('Content-type: multipart/form-data');
  require_once('../env.php');
  
  $server = getenv('SERVER');
  $db_name = getenv('DATABASE');
  $user_db = getenv('USER_DB');
  $password_db = getenv('PASSWORD_DB');
  $port_db = getenv('PORT_DB');
  
  $conexion = mysqli_connect($server, $user_db, $password_db, $db_name, $port_db) or die ("No se ha podido conectar al servidor de Base de datos");
  $db = mysqli_select_db($conexion, $db_name) or die ("Upps! Pues va a ser que no se ha podido conectar a la base de datos");
  
  
  $id_departamento = $_POST['id'];
  
  // Prepared statement para SELECT - SEGURIDAD SQL INJECTION
  $sqlProvincias = "SELECT * FROM provincia WHERE id_departamento = ? ORDER BY nombre_provincia ASC";
  $stmt = $conexion->prepare($sqlProvincias);
  
  if ($stmt === false) {
    die("Error en prepared statement: " . $conexion->error);
  }
  
  $stmt->bind_param("i", $id_departamento);
  $stmt->execute();
  $resultado = $stmt->get_result();

// echo $resultado; exit;
  
  $provincias = '<option value="0">Provincia</option>';
  while ($row = $resultado->fetch_array(MYSQLI_ASSOC)) {
    $provincias .= "<option value='$row[id]'>$row[nombre_provincia]</option>";
  }
  
  $stmt->close();
  echo $provincias;