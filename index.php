<?php

require_once('./vendor/autoload.php');
require_once('./config.php');

require_once('./src/app.php');

$route = new Route();
// var_dump($route);
// exit();


$route->get('/user', 'daf')->post('/', 'aa')->delete('/user', 'daf')->get('/user/:id', '路径参数')->get('/example/**', '通配符');

App::use(new Auth);

App::start();
