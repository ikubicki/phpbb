<?php

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require(__DIR__ . '/../vendor/autoload.php');

$app = new apps\auth\api(
    new phpbb\config('config/auth.json')
);
$app->addPlugin('db', 'ORM INSTANCE SHOULD BE HERE');

phpbb\serializer::serialize(
    $app->handle(new phpbb\request())
);