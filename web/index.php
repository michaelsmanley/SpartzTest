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

    $states = ORM::for_table('city')
        ->distinct()
        ->select('state')
        ->order_by_asc('state')
        ->find_array();

    $list = array_map(function($s) { return "/v1/states/{$s{'state'}}/cities.json"; }, $states);

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);
    $response->body(json_encode($list, JSON_UNESCAPED_SLASHES));
});


$app->get('/v1/states/:state/cities.json', function ($state) use ($app) {
    $app->log->debug("get cities in {$state}");

    $list = array();
    if (preg_match('/^[a-zA-z]{2}$/', $state)) {
        $cities = ORM::for_table('city')
            ->distinct()
            ->select('name')
            ->where('state', strtoupper($state))
            ->order_by_asc('name')
            ->find_array();

        $list = array_map(function($c) { return $c{'name'}; }, $cities);
    }

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);
    $response->body(json_encode($list, JSON_UNESCAPED_SLASHES));
});


$app->get('/v1/states/:state/cities/:city.json', function ($state, $city) use ($app) {
    $radius = $app->request->get('radius') ? $app->request->get('radius') : 0;
    $app->log->debug("get cities within {$radius} miles of {$city} in {$state}");

    $list = array();
    if (preg_match('/^[a-zA-z]{2}$/', $state) &&
        preg_match('/^[\w\s]*$/', $city)) {
        $city = Model::factory('\\MSMP\\Spartz\\City')
            ->where('name', trim($city))
            ->where('state', strtoupper($state))
            ->find_one();

        if (($city instanceof \MSMP\Spartz\City) && (intval($radius) != 0)) {
            $cts = $city->nearby(intval($radius));
            $list = array_map(function($c) { return $c->name; }, $cts);
        }
    }

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);
    $response->body(json_encode($list, JSON_UNESCAPED_SLASHES));
});


$app->get('/v1/users', function () use ($app) {
    $app->log->debug("get list of users");

    $users = Model::factory('\\MSMP\\Spartz\\User')
        ->find_many();

    $list = array_map(function($u) { return $u->as_array(); }, $users);

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);
    $response->body(json_encode($list, JSON_UNESCAPED_SLASHES));
});


$app->get('/v1/users/:uid/visits', function ($uid) use ($app) {
    $app->log->debug("get visits for user {$uid}");

    $user = Model::factory('\\MSMP\\Spartz\\User')
        ->where('id', intval($uid))
        ->find_one();

    $cities = array();
    if ($user instanceof \MSMP\Spartz\User)
        $cities = $user->cities();

    $list = array_map(function($c) { return $c->name; }, $cities);

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);
    $response->body(json_encode($list, JSON_UNESCAPED_SLASHES));
});

$app->post('/v1/users/:uid/visits', function ($uid) use ($app) {
    $app->log->debug("add visit for user {$uid}");

    if ($app->request->isAjax()) {
        $state = null;
        $city = null;
        $body = json_decode($app->request->getBody(), true);
        if (! is_null($body) && is_array($body)) {
            $state = (array_key_exists('state', $body)) ? $body['state'] : null;
            $city = (array_key_exists('city', $body)) ? $body['city'] : null;
        }
    } else {
        # standard POST
        $state = $app->request->post('state');
        $city = $app->request->post('city');
    }

    $user = Model::factory('\\MSMP\\Spartz\\User')
        ->where('id', intval($uid))
        ->find_one();

    if (preg_match('/^[a-zA-z]{2}$/', $state) &&
        preg_match('/^[\w\s]*$/', $city)) {
        $city = Model::factory('\\MSMP\\Spartz\\City')
            ->where('state', strtoupper($state))
            ->where('name', trim($city))
            ->find_one();
    } else {
        $city = null;
    }

    if (($user instanceof \MSMP\Spartz\User) && ($city instanceof \MSMP\Spartz\City)) {
        $visit = Model::factory('\\MSMP\\Spartz\\Visit')
            ->where('user_id', $user->id)
            ->where('city_id', $city->id)
            ->find_one();
        if (!$visit) {
            $visit = Model::factory('\\MSMP\\Spartz\\Visit')->create();
            $visit->user_id = $user->id;
            $visit->city_id = $city->id;
            $visit->save();
        }
        $result = TRUE;
        $app->log->debug("visit result TRUE");
    } else {
        $result = FALSE;
        $app->log->debug("visit result FALSE");
    }

    if ($app->request->isAjax()) {
        $response = $app->response();
        $response['Content-Type'] = 'application/json';
        $response->status(200);
        $status = $result ? 'SUCCEEDED' : 'FAILED';
        $response->body(json_encode(array('status' => $status), JSON_UNESCAPED_SLASHES));
    } else {
        # what to do with a regular POST wasn't specified, so let's do the least
        echo $result ? 'UPDATE SUCCEEDED' : 'UPDATE FAILED';
    }
});

$app->run();
