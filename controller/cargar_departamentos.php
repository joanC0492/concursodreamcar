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
  
  
  $sqlDepartamentos = "SELECT * FROM departamentos ORDER BY nombre_departamento ASC";
  $resultado = $conexion->query($sqlDepartamentos);

  //echo $resultado; exit;
  
  $departamentos = "<option value='0'>Departamento</option>";
  while ($row = $resultado->fetch_array(MYSQLI_ASSOC)) {
    $departamentos .= "<option value='$row[id]'>$row[nombre_departamento]</option>";
  }
  
  echo $departamentos;

