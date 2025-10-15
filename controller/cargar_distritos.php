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
  
  
  $id_provincia = $_POST['id'];
  
  // Prepared statement para SELECT - SEGURIDAD SQL INJECTION
  $sqlDistritos = "SELECT * FROM distrito WHERE id_provincia = ? ORDER BY nombre_distrito ASC";
  $stmt = $conexion->prepare($sqlDistritos);
  
  if ($stmt === false) {
    die("Error en prepared statement: " . $conexion->error);
  }
  
  $stmt->bind_param("i", $id_provincia);
  $stmt->execute();
  $resultado = $stmt->get_result();
  
  
  $distritos = '<option value="0">Distrito</option>';
  while ($row = $resultado->fetch_array(MYSQLI_ASSOC)) {
    $distritos .= "<option value='$row[id]'>$row[nombre_distrito]</option>";
  }
  
  $stmt->close();
  echo $distritos;