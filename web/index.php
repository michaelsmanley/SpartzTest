<?php
require '../vendor/autoload.php';

$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => array(
        new \Monolog\Handler\StreamHandler('/tmp/spartztest-'.date('Y-m-d').'.log'),
    ),
));

ORM::configure('mysql:host=localhost;dbname=spartztest');
ORM::configure('username', 'spartztest');
ORM::configure('password', 'spartztest');


$app = new \Slim\Slim(array(
	'log.enabled' => true,
	'log.level'   => \Slim\Log::DEBUG,
	'log.writer'  => $logger,
	'debug'       => true,
	'mode'        => 'development',
));
$app->setName('spartztest');

$app->get('/', function () use ($app) {
	$app->log->debug("redirecting root request");
	$app->redirect('/v1/states');
});

$app->get('/v1/states', function () use ($app) {
	$app->log->debug("get list of states");

	$sts = ORM::for_table('city')->distinct()->select('state')->order_by_asc('state')->find_array();
	$f = function($s) { return "/v1/states/{$s{'state'}}/cities.json"; };
	$states = array_map($f, $sts);

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
	$response->status(200);
	$response->body(json_encode($states, JSON_UNESCAPED_SLASHES));
});

$app->get('/v1/states/:state/cities.json', function ($state) use ($app) {
	$app->log->debug("get cities in {$state}");
	
	$cts = ORM::for_table('city')->distinct()->select('name')->where('state', $state)->order_by_asc('name')->find_array();
	$f = function($c) { return $c{'name'}; };
	$cities = array_map($f, $cts);

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
	$response->status(200);
	$response->body(json_encode($cities, JSON_UNESCAPED_SLASHES));
});

$app->get('/v1/states/:state/cities/:city.json', function ($state, $city) use ($app) {
	$radius = $app->request->get('radius') ? $app->request->get('radius') : 0;
	$app->log->debug("get cities within {$radius} miles of {$city} in {$state}");
	echo "get cities within {$radius} miles of {$city} in {$state}" ;
});

$app->get('/v1/users', function () use ($app) {
	$app->log->debug("get list of users");
	echo "get list of users";
});

$app->get('/v1/users/:user/visits', function ($user) use ($app) {
	$app->log->debug("get visits for user {$user}");
	echo "get visits for user {$user}" ;
});

$app->post('/v1/users/:user/visits', function ($user) use ($app) {
	$app->log->debug("add visit for user {$user}");
	echo "add visit for user {$user}" ;
});

$app->run();
