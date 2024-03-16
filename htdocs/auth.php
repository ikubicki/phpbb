<?php

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require(__DIR__ . '/../vendor/autoload.php');

phpbb\config::root(__DIR__ . '/..');

$config = new phpbb\config('config/auth.json');
$db = new phpbb\db($config);
$app = new apps\auth\api($config);
$app->addPlugin('db', $db);

phpbb\serializer::serialize(
    $app->handle(new phpbb\request())
);