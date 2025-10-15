<?php
	require_once('env.php');
	require_once('funciones_cifrado.php'); // Para descifrar DNI en reportes

	$server = getenv('SERVER');
	$dbname = getenv('DATABASE');
	$user = getenv('USER_DB');
	$password = getenv('PASSWORD_DB');


	$db = new PDO("mysql:host=".$server.";dbname=".$dbname,$user,$password);
	$db->exec("set names utf8");
	$declaracion = $db->prepare("SELECT * FROM dreamcar_registros where fecha_registro >= '2024-10-10'");

	$declaracion->execute();

	$lista =  $declaracion->fetchAll();
	$nombre_archivo = date("YmdHis").".xls";

	header('Content-Encoding: UTF-8');
	header("Content-Type: application/vnd.ms-excel");
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-Disposition: attachment;filename="'.$nombre_archivo.'"');

	// Encabezados actualizados - campos sensibles eliminados
	$html = "<table><tr><th>Codigo</th><th>Nombre</th><th>Apellido</th><th>Nacimiento</th><th>DNI</th><th>Sexo</th><th>Fecha registro</th></tr>";

	foreach ($lista as $value) {
		$html.="<tr>";
		$html.="<td>".$value["codigo"]."</td>";
		$html.="<td>".$value["dc_nombre"]."</td>";
		$html.="<td>".$value["dc_apellido"]."</td>";
		$html.="<td>".$value["dc_fecha"]."</td>";
		// Descifrar DNI para mostrar en reporte (solo administradores autorizados)
		$dni_descifrado = descifrarDNI($value["dc_dni"]);
		$html.="<td>".$dni_descifrado."</td>";
		$html.="<td>".$value["dc_sexo"]."</td>";
		$html.="<td>".$value["fecha_registro"]."</td>";
		$html.="</tr>";
	}

	$html.= "</table>";

	echo utf8_decode($html);
	die();
?>