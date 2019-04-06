<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

if (
    isset($_SERVER['HTTP_CLIENT_IP']) ||
    isset($_SERVER['HTTP_X_FORWARDED_FOR']) ||
    'cli-server' === php_sapi_name() ||
    !(
        in_array(
            @$_SERVER['REMOTE_ADDR'],
            [
                '127.0.0.1',
                '::1',
            ]
        ) ||
        false !== strpos(// Docker
            @$_SERVER['REMOTE_ADDR'],
            '172.'
        )
    )
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
