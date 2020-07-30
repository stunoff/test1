<?php
session_start();
require_once __DIR__."/config.php";
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/app/Core/functions.php";

use App\Core\Request;
use App\Core\Router;

$request = new Request();
$router = new Router($request);
$router->getRoutes();
$router->run();
