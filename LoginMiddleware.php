<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
 
require_once 'Users.php';
 
class LoginMiddleware { //extends \Slim\Middleware {

	private $conn;

	public function __construct($conn) {
		$this->conn = $conn;
	}
		
	public function __invoke(Request $request, Response $response, Callable $next) {
		$datos_request = $request->getParsedBody('JSON');
		$login = (string)$datos_request["login"];
		$pass = (string)$datos_request["pass"];
		
		//comprobar si el usuario y la contraseña estan en la bbdd
		$esUsuario = $this->comprobarUsuario($login, $pass);
		
		if ($esUsuario) {
			$response = $next($request, $response);		
		} else {
			$datos = array("resultado" => "Acceso no Permitido ".$login);
			$response->getBody()->write(json_encode($datos));
		}		
		return $response;
	}
	
	private function comprobarUsuario($login, $pass){
		
		$user = \Users::where('usr_login',$login)
						->where('usr_pass',$pass)
						->get();
		
		//echo "en el login middleware:".$login.";".$pass;
		
		$retorno = false;		
		if (count($user) > 0) {
			$retorno = true;
		} 		
		return $retorno;
	}
}