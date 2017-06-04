<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;

$config['db']['host']   = "localhost";
$config['db']['user']   = "user";
$config['db']['pass']   = "password";
$config['db']['dbname'] = "exampleapp";

/*
$config['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
*/

$app = new \Slim\App(["settings" => $config]);

$mw = function ($request, $response, $next) {
    $response->getBody()->write('<h1>Middleware Uno</h1>');
    $response = $next($request, $response);
    $response->getBody()->write('<br/>Final Uno');

    return $response;
};

$mw2 = function ($request, $response, $next) {
    $response->getBody()->write('<h2>Middleware Dos</h2>');
    $response = $next($request, $response);
    $response->getBody()->write('<br/>Final Dos');

    return $response;
};

$authMiddleware = function ($request, $response, $next) {
    $response->getBody()->write('<h2>Auth Middleware</h2>');
    //$response = $next($request, $response);
	
	$response->getBody()->write($request->getAttribute('nombre'));
	
	$nombre = $request->getAttribute('nombre');
	if ($nombre == "jose") {
		$response = $next($request, $response);
	} else {
		$response->getBody()->write("<br/>No autorizado: $nombre");
	}	
    $response->getBody()->write('<br/>Final tres');

    return $response;
};

$app->get('/hello/{name:[a-zA-Z]*}[/{apellido}]', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
	$apellido = $request->getAttribute('apellido');
    $response->getBody()->write("Hello, $name $apellido");
	//$this->logger->addInfo("Ruta 1");
    return $response;
})->add($mw2);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello world");
	//$this->logger->addInfo("Ruta sin HELLO");
    return $response;
})->add($mw);

$app->group("/api",function() use($app,$mw2) {
	$this->get("/funcUno/{nombre}",function (Request $request, Response $response){
		$nombre = $request->getAttribute('nombre');
		$response->getBody()->write("<h3>FuncUno</h3> $nombre");
		return $response;
	})->setName('funcUnoName');
	$this->get("/funcDos/{nombre}",function (Request $request, Response $response){
		$nombre = $request->getAttribute('nombre');
		$response->getBody()->write("<h3>FuncDos</h3> $nombre");
		return $response;
	})->add($mw2);
	$this->get("/redirectUno",function (Request $request, Response $response) use ($app){
		echo $app->pathFor('funcUnoName', array("nombre" => "Jose Angel"));
	})->add($mw2);
});

//20161202: funciones de prueba
$app->get("/apiJam/{nombre}",function(Request $request, Response $response) use($mw2) { 
	$response->getBody()->write("<h3>Función del API JAM</h3>");
})->add($authMiddleware);

$app->run();