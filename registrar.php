<?php  
	header('Content-type: multipart/form-data');
	session_start();
	require_once('env.php');
	//importamos clases para Mail
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	//Load Composer's autoloader
	require 'phpmailer/Exception.php';
	require 'phpmailer/PHPMailer.php';
	require 'phpmailer/SMTP.php';

	$dominio = $_SERVER['HTTP_HOST'];
	$server = getenv('SERVER');
	$db_name = getenv('DATABASE');
	$user_db = getenv('USER_DB');
	$password_db = getenv('PASSWORD_DB');
  	$port_db = getenv('PORT_DB');
	$usuario_correo = getenv('USUARIO_CORREO');
	$password_correo = getenv('PASSWORD_CORREO');
	$secret_google_captcha = getenv('PRIVATE_KEY_CAPTCHA');
	$host_mail_server = getenv('HOST_MAIL_SERVER');
	$port_mail_server = getenv('PORT_MAIL_SERVER');

  /* recaptcha */
	$recaptcha = $_POST['dc_recaptcha'];
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => $secret_google_captcha,
		'response' => $recaptcha
	);

	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data),
			'header' => 'Content-Type: application/x-www-form-urlencoded'
		)
	);

	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success = json_decode($verify);
	
	if ( !$captcha_success->success ) {
		echo 'Debe verificar el captcha'; exit;
	} 
	/* end recaptcha */

  $conexion = mysqli_connect($server, $user_db, $password_db, $db_name, $port_db) or die("No se ha podido conectar al servidor de Base de datos");  
  
  $db = mysqli_select_db( $conexion, $db_name ) or die("Upps! Pues va a ser que no se ha podido conectar a la base de datos");


  	$_SESSION['id_utm'] = $_POST['id_utm'];
	// datos
	$dc_categoria = $_POST['dc_categoria'];
	$dc_nombre = $_POST['dc_nombre'];
	$dc_apellido = $_POST['dc_apellido'];
	$dc_edad = $_POST['dc_edad'];
	$dc_fecha = $_POST['dc_fecha'];
	$dc_dni = $_POST['dc_dni'];
	$dc_sexo = $_POST['dc_sexo'];
	$dc_apoderado_name = $_POST['dc_apoderado_name'];
	$dc_apoderado_apellido = $_POST['dc_apoderado_apellido'];
	$dc_apoderado_pais = $_POST['dc_apoderado_pais'];
	$dc_apoderado_fecha = $_POST['dc_apoderado_fecha'];
	$dc_apoderado_dni = $_POST['dc_apoderado_dni'];
	$dc_apoderado_celular = empty($_POST['dc_apoderado_celular']) ? 'null' : $_POST['dc_apoderado_celular'];
	$dc_apoderado_telefono = empty($_POST['dc_apoderado_telefono']) ? 'null' : $_POST['dc_apoderado_codigo_telefono'] . $_POST['dc_apoderado_telefono'];
	$dc_apoderado_direccion = $_POST['dc_apoderado_direccion'];
	$dc_apoderado_email = $_POST['dc_apoderado_email'];
	$dc_dibujo_titulo = $_POST['dc_dibujo_titulo'];
	$dc_dibujo_desc = $_POST['dc_dibujo_desc'];  
	$dc_term = $_POST['dc_term'];
	$dc_extension = $_POST['dc_extension'];
	$dc_departamento_id = $_POST['dc_departamento_id'];
	$dc_dibujo_categoria = $_POST['dc_dibujo_categoria'];
	$dc_provincia_id = $_POST['dc_provincia_id'];
	$dc_distrito_id = $_POST['dc_distrito_id'];
	$dc_dni_file_extension = $_POST['dc_dni_file_extension'];
  $dc_dni_nino_file_extension = $_POST['dc_dni_nino_file_extension'];
	$dc_colegio = $_POST['dc_colegio'];

	//var_dump($_POST);exit;
	
	// === FUNCIONES DE VALIDACIÓN DE ARCHIVOS - SEGURIDAD ===
	function validarArchivoImagen($archivo) {
		// Validar que el archivo existe
		if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
			return array('valido' => false, 'mensaje' => 'Error en la carga del archivo');
		}
		
		// Validar tamaño máximo (5MB)
		$tamaño_maximo = 5 * 1024 * 1024; // 5MB en bytes
		if ($archivo['size'] > $tamaño_maximo) {
			return array('valido' => false, 'mensaje' => 'El archivo excede el tamaño máximo de 5MB');
		}
		
		// Validar MIME type real
		$tipos_permitidos = array('image/jpeg', 'image/jpg', 'image/png');
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_real = finfo_file($finfo, $archivo['tmp_name']);
		finfo_close($finfo);
		
		if (!in_array($mime_real, $tipos_permitidos)) {
			return array('valido' => false, 'mensaje' => 'Tipo de archivo no permitido. Solo se aceptan JPG, JPEG y PNG');
		}
		
		// Validar extensión
		$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
		$extensiones_permitidas = array('jpg', 'jpeg', 'png');
		if (!in_array($extension, $extensiones_permitidas)) {
			return array('valido' => false, 'mensaje' => 'Extensión de archivo no permitida');
		}
		
		return array('valido' => true, 'extension' => $extension, 'mime' => $mime_real);
	}
	
	function generarNombreAleatorio($extension) {
		return bin2hex(random_bytes(16)) . '.' . $extension;
	}
	
	/** upload seguro **/
	/** upload seguro **/
	
	// Validar archivo de dibujo
	$validacion_dibujo = validarArchivoImagen($_FILES['dc_files']);
	if (!$validacion_dibujo['valido']) {
		echo "Error en archivo de dibujo: " . $validacion_dibujo['mensaje']; 
		exit;
	}
	
	// Validar archivo DNI del apoderado
	$validacion_dni = validarArchivoImagen($_FILES['dc_dni_file']);
	if (!$validacion_dni['valido']) {
		echo "Error en archivo DNI apoderado: " . $validacion_dni['mensaje']; 
		exit;
	}
	
	// Validar archivo DNI del niño
	$validacion_dni_nino = validarArchivoImagen($_FILES['dc_dni_nino_file']);
	if (!$validacion_dni_nino['valido']) {
		echo "Error en archivo DNI del participante: " . $validacion_dni_nino['mensaje']; 
		exit;
	}
	
	// Generar nombres aleatorios seguros
	$file_name = generarNombreAleatorio($validacion_dibujo['extension']);
	$file_name_dni = generarNombreAleatorio($validacion_dni['extension']);
	$file_name_dni_nino = generarNombreAleatorio($validacion_dni_nino['extension']);
	
	$carpeta = __DIR__ . "/images/concurso_2025/CAT_".$dc_categoria;

	// Carpeta que se creara por cada participante que se registre en el concurso
	// En esta carpeta se guardará la info del concursante, se generará una carpeta por cada concursante inscrito
	$carpeta_participante = $carpeta."/".$dc_dni;

	// Verificar existencia de la carpeta, de no ser el caso entonces se crea la carpeta
	if ( !file_exists( $carpeta_participante ) ) {
		if(!mkdir($carpeta_participante, 0755, true)) { // Permisos más restrictivos
			echo "No se pudo crear la carpeta"; exit;
		}
	}

	// Rutas de destino
	$filepath = $carpeta_participante.'/'.$file_name;
	$filepath_dni = $carpeta_participante.'/'.$file_name_dni;
	$filepath_dni_nino = $carpeta_participante.'/'.$file_name_dni_nino;

	// Guardar archivos usando move_uploaded_file() - SEGURO
	if (!move_uploaded_file($_FILES['dc_files']['tmp_name'], $filepath)) {
		echo "Error: No se pudo guardar la imagen del concursante";
		exit;
	}
	
	if (!move_uploaded_file($_FILES['dc_dni_file']['tmp_name'], $filepath_dni)) {
		echo "Error: No se pudo guardar el DNI del apoderado";
		exit;
	}
	
	if (!move_uploaded_file($_FILES['dc_dni_nino_file']['tmp_name'], $filepath_dni_nino)) {
		echo "Error: No se pudo guardar el DNI del participante";
		exit;
	}


	$fecha_registro = date('Y-m-d');
	$dc_term_value = 1; // Variable para el valor constante
	
	// Prepared statement para INSERT - SEGURIDAD SQL INJECTION
	$sql = "INSERT INTO dreamcar_registros ( 
		codigo, 
		dc_categoria, 
		dc_nombre, 
		dc_apellido, 
		dc_edad, 
		dc_fecha, 
		dc_dni, 
		dc_sexo, 
		dc_apoderado_name,
		dc_apoderado_apellido,
		dc_apoderado_pais, 
		dc_apoderado_fecha, 
		dc_apoderado_dni, 
		dc_apoderado_celular, 
		dc_apoderado_telefono, 
		dc_apoderado_direccion, 
		dc_apoderado_email, 
		dc_dibujo_titulo, 
		dc_dibujo_desc, 
		dc_files, 
		dc_term, 
		fecha_registro,
		dc_dibujo_categoria, 
		dc_departamento_id, 
		dc_provincia_id, 
		dc_distrito_id,
		dc_dni_file,
    dc_dni_nino_file,
		dc_colegio
	) VALUES ( 
		NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
	)";

	$stmt = $conexion->prepare($sql);
	if ($stmt === false) {
		die("Error en prepared statement: " . $conexion->error);
	}
	
	// Bind parameters: i=integer, s=string, d=decimal
	$stmt->bind_param("issssissssssssssssiiiissssss", 
		$dc_categoria,
		$dc_nombre, 
		$dc_apellido, 
		$dc_edad, 
		$dc_fecha, 
		$dc_dni,
		$dc_sexo, 
		$dc_apoderado_name, 
		$dc_apoderado_apellido,
		$dc_apoderado_pais, 
		$dc_apoderado_fecha, 
		$dc_apoderado_dni, 
		$dc_apoderado_celular, 
		$dc_apoderado_telefono, 
		$dc_apoderado_direccion, 
		$dc_apoderado_email, 
		$dc_dibujo_titulo, 
		$dc_dibujo_desc, 
		$file_name,
		$dc_term_value,
		$fecha_registro,
		$dc_dibujo_categoria,
		$dc_departamento_id, 
		$dc_provincia_id, 
		$dc_distrito_id,
		$file_name_dni,
		$file_name_dni_nino,
		$dc_colegio
	);

	if ($stmt->execute()) {
		$idinsert = $conexion->insert_id;
		$stmt->close();
		
		$codigo = "DC00".$idinsert;
		
		// Prepared statement para UPDATE - SEGURIDAD SQL INJECTION
		$sql_update = "UPDATE dreamcar_registros SET codigo = ? WHERE id = ?";
		$stmt_update = $conexion->prepare($sql_update);
		if ($stmt_update === false) {
			die("Error en prepared statement UPDATE: " . $conexion->error);
		}
		
		$stmt_update->bind_param("si", $codigo, $idinsert);
		
		//obtemos el codigo para el correo
		$html_email = '<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="format-detection" content="telephone=no">
			<meta name="x-apple-disable-message-reformatting">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
		</head>
		<body>
			<center>
				<table width="600px" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff; color:#000000">
					<tr>
						<td>
							<img src="https://'.$dominio.'/concursodreamcar/images/mailing_template/mailing_v3_01.png?v=1" alt="" width="600" height="312"  style="border:0;display:block;outline:none;text-decoration:none;">
						</td>
					</tr>
					<tr>
						<td align="center"  style=" border: inset 0pt">
							<table width="600px" border="0" cellpadding="0" cellspacing="0">
								<td align="center" width="175" height="54"  style=" border: inset 0pt">
									<img src="https://'.$dominio.'/concursodreamcar/images/mailing_template/mailing_v3_02.png?v=1" alt="" width="175" height="54"  style="border:0;display:block;outline:none;text-decoration:none;height:auto;">
								</td>
								<td align="center" width="236" height="54" style="background-color:#ffffff" background="#ffffff">
									<p style="font-size:30px;line-height: 30px;color:#000000;margin:0px;font-family:sans-serif" color="#000000"><strong>#'.$codigo.'</strong></p>
								</td>
								<td align="center" width="189" height="54" style=" border: inset 0pt">
									<img src="https://'.$dominio.'/concursodreamcar/images/mailing_template/mailing_v3_03.png?v=1" alt="" width="189" height="54"  style="border:0;display:block;outline:none;text-decoration:none;">
								</td>
							</table>
						</td>
					</tr>
					<tr>
						<td style=" border: inset 0pt">
							<img src="https://'.$dominio.'/concursodreamcar/images/mailing_template/mailing_v3_04.png?v=1" alt="" width="600" height="534"  style="border:0;display:block;outline:none;text-decoration:none;">
						</td>
					</tr>
				</table>
			</center>
		</body>';

		if ($stmt_update->execute()) {
			$stmt_update->close();
			//enviamor correo
			$mail = new PHPMailer(true);
			try {
				//Server settings
				$mail->SMTPDebug = 0;                      //Enable verbose debug output
				$mail->isSMTP();                                            //Send using SMTP
				$mail->Host       = $host_mail_server;                     //Set the SMTP server to send through
				$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
				$mail->Username   = $usuario_correo;                     //SMTP username
				$mail->Password   = $password_correo;                               //SMTP password
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
				$mail->Port       = $port_mail_server;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		
				//Recipients
				$mail->setFrom($usuario_correo, 'Toyota Dream Car - El Auto Soñado Toyota');
				$mail->addAddress($_POST['dc_apoderado_email']);     //Add a recipient
				$mail->addBCC('dreamcar@latinbrands.pe');
				$mail->addBCC('dreamcar.mediaimpact@gmail.com');
				//Content
				$mail->isHTML(true);                                  //Set email format to HTML
				$mail->CharSet = 'UTF-8';
				$mail->Subject = 'Gracias por tu Inscripción';
				$mail->Body    = $html_email;
				$mail->AltBody = 'Toyota Dream Car';
		
				$mail->send();
				echo 'RCT'; exit;
			} catch (Exception $e) {
					echo "No se pudo enviar correo de confirmación: {$mail->ErrorInfo}"; exit;
			}

		} else {
			echo "Error: No se pudo generar codigo del concursante"; exit;
		}
		// echo "New record created successfully";
	} else {
		$stmt->close();
		//var_dump($conexion->error);
		echo "Error: No se pudo registrar al concursante"; exit;
		// header("Location: index.php");
	}
?>