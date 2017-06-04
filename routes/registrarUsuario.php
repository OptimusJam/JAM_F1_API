<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once 'Users.php';

$app->post('/registrarUsuario', function(Request $request, Response $response) {
	
	$datos_request = $request->getParsedBody('JSON');
	$name = (string)$datos_request["name"]; 
	$ape_1 = (string)$datos_request["ape_1"]; 
	$ape_2 = (string)$datos_request["ape_2"]; 
	$email = (string)$datos_request["email"]; 
	$login = (string)$datos_request["login"]; 
	$pass = (string)$datos_request["pass"]; 
	
	//Validacion de los parametros del usuarios
	$usuarioCorrecto = true;
	
	//si los parametros son correctos, se inserta en BBDD
	if ($usuarioCorrecto) {
		$usuario = new \Users;
		$usuario->usr_nom = $name;
		$usuario->usr_ape_1 = $ape_1;
		$usuario->usr_ape_2 = $ape_2;
		$usuario->usr_email = $email;
		$usuario->usr_login = $login;
		$usuario->usr_pass = $pass;
		$usuario->save();
		$datos = array("resultado" => $usuario->id);
		return json_encode($datos);
	} 	
});