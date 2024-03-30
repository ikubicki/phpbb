<?php

namespace phpbb;

use apps;

require(__DIR__ . '/include.php');

$request = new request();

serializer::start($request);

$config = new config('config/auth.json');
$app = new apps\auth\api($config);
$app->addPlugin('db', new db($config));

serializer::serialize($app->handle($request));