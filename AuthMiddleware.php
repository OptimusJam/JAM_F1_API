<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
 
require_once 'Users.php';
 
class AuthMiddleware { //extends \Slim\Middleware {

	private $conn;

	public function __construct($conn) {
		$this->conn = $conn;
	}
		
	public function __invoke(Request $request, Response $response, Callable $next) {
		$datos_request = $request->getParsedBody('JSON');
		
		if (array_key_exists ("token",datos_request)) {
			//Comprobar que la sesion es valida -> para avanzar a otra operacion
			$token = $datos_request["token"];
			$response = $next($request, $response);
		} else if (array_key_exists ("login",datos_request) && (array_key_exists ("pass",datos_request)) {
			//Hacemos un login
			$login = (string)$datos_request["login"];
			$pass = (string)$datos_request["pass"];
			
			$esUsuario = $this->comprobarUsuario($login, $pass);
			
			if ($esUsuario) {
				$response = $next($request, $response);		
			} else {
				$datos = array("resultado" => "Acceso no Permitido ".$login);
				$response->getBody()->write(json_encode($datos));
			}
		}
	
		return $response;
	}
	
	private function comprobarUsuario($login, $pass){
		
		$users = \Users::where('usr_login',$login)
						->where('usr_pass',$pass)
						->get();
		
		$retorno = false;		
		if (count($users) > 0) {
			$retorno = true;
		} 		
		return $retorno;
	}
}