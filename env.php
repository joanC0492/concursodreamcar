<?php

// base de datos
$server = "173.201.181.154"; // dirección del servidor, ejemplo: localhost
$database = "dreamcar"; // digitar nombre de la base de datos usada actualmente
$port = "3306";
$password_db = "NOTEPADmax12!"; // si tiene, password de la base de datos, en este caso no tengo BD

// mail de confirmación
$usuario_cuenta_correo = "max.carrasco.h@gmail.com"; // cuenta de correo para enviar el mail de confirmación
$usuario_password_correo = "hxjzhtniwxnptcjq"; // contraseña para del correo

// configurar usuario de la base de datos
$user_db = "dreamcar"; // usuario de la base de datos

// parámetros para servidor de correo utilizado
$host_mail_server = "smtp.gmail.com"; // ejemplo: gmail smtp.gmail.com
$port_mail_server = 587; // puerto usado por el servidor de correo ejm: 587 para gmail

// parámetros para establecer credenciales de google captcha
$public_google_captcha = "6Lfa3dwrAAAAAPB0-MBZAe7oJ26Yw914wfnUfQ5X"; // clave pública para google captcha
$secret_google_captcha = "6Lfa3dwrAAAAAH1l4uKjWM9kJa912q9MBovPOYCy"; // clave pública para google captcha

putenv("SERVER=$server");
putenv("DATABASE=$database");
putenv("PASSWORD_DB=$password_db");
putenv("USUARIO_CORREO=$usuario_cuenta_correo");
putenv("PASSWORD_CORREO=$usuario_password_correo");

putenv("USER_DB=$user_db");

putenv("PUBLIC_KEY_CAPTCHA=$public_google_captcha");
putenv("PRIVATE_KEY_CAPTCHA=$secret_google_captcha");

putenv("HOST_MAIL_SERVER=$host_mail_server");
putenv("PORT_MAIL_SERVER=$port_mail_server");


