<?php

namespace phpbb;

use apps;

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require(__DIR__ . '/../vendor/autoload.php');

config::root(__DIR__ . '/..');

$request = new request();

serializer::start($request);

$config = new config('config/auth.json');
$db = new db($config);
$app = new apps\auth\api($config);
$app->addPlugin('db', $db);

serializer::serialize($app->handle($request));