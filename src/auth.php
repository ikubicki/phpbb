<?php

require(__DIR__ . '/../vendor/autoload.php');

if ($argc > 0) {
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
}
$_SERVER['REQUEST_METHOD'] = $argv[1] ?? null;
$_SERVER['REQUEST_URI'] = $argv[2] ?? null;
if ($argv[3] ?? false) {
    $_POST = json_decode($argv[3], true);
}


$_SERVER['HTTP_AUTHORIZATION'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6ImI3Y2E0N2MyLWEzYTUtNDg2Yi1iOTdlLTBhN2FiZDNkMGU4YSJ9.eyJzdWIiOiI2NWVkOTA5N2JmODMxNjAxZDU0MzBmNGIiLCJpc3MiOiJsb2NhbGhvc3QiLCJleHAiOjE3MTQwODU3MTh9.nH_z4CcqLBbAkqeRoCYQRk8EtoH8nG6X-Uwz35yCozSg5xG56BXIS3KYw3eADDwx89JgGz2wRTXsrCndH8Wo6w';

$app = new apps\auth\api(
    new phpbb\config('config/auth.json')
);
$app->addPlugin('db', 'ORM INSTANCE SHOULD BE HERE');

phpbb\serializer::serialize(
    $app->handle(new phpbb\request())
);