<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

require_once 'vendor/autoload.php';
require_once 'AuthMiddleware.php';
require_once 'LoginMiddleware.php';
require_once 'Users.php';

$config['displayErrorDetails'] = true;

//Configuracion de la base de datos
// Database information
$config_db = array(
    'driver' 	=> 'mysql',
    'host' 		=> '127.0.0.1',
    'database' 	=> 'jamf1',
    'username' 	=> 'root',
    'password' 	=> 'root',
    'collation' => 'utf8_general_ci',
    'prefix' 	=> '',
    'charset'   => 'utf8',
);

$container = new Illuminate\Container\Container;
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$conn = $connFactory->make($config_db);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');
\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

//v 2.0: $app = new \Slim\Slim();
//v 3.0
$app = new \Slim\App(["settings" => $config]);

$authMiddleware = new AuthMiddleware($conn);
$objLoginMidd = new LoginMiddleware($conn);

//$app->add(new AuthMiddleware());

$app->get('/', function(Request $request, Response $response) {
	
	//$users = \Users::all();
	$users = \Users::where('usr_login','user3')->get();
	
	if (count($users) > 0) {
		$retorno = array("resultado"=>"Acceso Correcto",
				"datos"=>$users);
	} else {
		$retorno = array("resultado"=>"Acceso Denegado",
				"datos"=>"Sin usuarios");		
	}
	return json_encode($retorno);
});

$app->post('/login', function(Request $request, Response $response) {
	$time = time();
	$key = 'private_key_jam_f1_v2';
	
	$datos_request = $request->getParsedBody('JSON');
	$login = (string)$datos_request["login"]; 
	$pass = (string)$datos_request["pass"]; 
	
	$token = array(
		'iat' => $time, // Tiempo que inició el token
		'exp' => $time + (60*60), // Tiempo que expirará el token (+1 hora)
		'data' => [ // información del usuario
			'login' => $login,
			'pass' => $pass
		]
	);

	$resultado = array("resultado" => JWT::encode($token,$key));

	$response->getBody()->write(json_encode($resultado));
	return $response;
	
})->add($authMiddleware);

require 'routes/registrarUsuario.php';

$app->group("/inicio",function() use($app) {
	
	$this->post('/user/',function(Request $request, Response $response) {
		//$name = $request->getAttribute('name');		
		$datos = array("resultado" => "Bienvenido");
		$response->getBody()->write(json_encode($datos));
		return $response;
	});

	$this->post("/campeonato/", function(Request $request, Response $response) {
		//$name = $request->getAttribute('name');
		$datos = array("resultado" => "Campeonato");		
		$response->getBody()->write(json_encode($datos));
		return $response;
	});
	
	$this->post("/broker/", function(Request $request, Response $response) {
		//$name = $request->getAttribute('name');
		$datos = array("resultado" => "broker");		
		$response->getBody()->write(json_encode($datos));
		return $response;
	});
	
	$this->post("/centro_estadistico/", function(Request $request, Response $response) {
		//$name = $request->getAttribute('name');
		$datos = array("resultado" => "Centro Estadistico");		
		$response->getBody()->write(json_encode($datos));
		return $response;
	});

})->add($authMiddleware);

$app->run();