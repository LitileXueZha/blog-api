<?php

require_once('./vendor/autoload.php');
require_once('./config.php');

require_once('./src/app.php');

$route = new Route();
// var_dump($route);
// exit();

function aa($req) {
    Log::debug($req);
}

$route
->get('/no', 'aa')
->get('/user', 'aa')
->delete('/user', 'aa')
->get('/user/:id', 'aa')
->get('/example/**', 'aa')
->get('/', 'aa');

App::use(new Auth);
App::use(new RouteMiddleware($route));

App::start();
