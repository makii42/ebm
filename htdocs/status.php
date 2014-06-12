<?php
/**
 * This is a reimplementation of proxy.php that uses SILEX to proxy Jenkins reads and
 * also allows a more grained auth configuration.
 */

require_once __DIR__ . '../vendor/autoload.php';

$app = new Silex\Application();
$env = getenv('APP_ENV') ?: 'prod';

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../config/$env.json"));

$app->get(
        '/status/{hostLabel}/{jobName}',
        function ($hostLabel, $jobName) use ($app)
        {

        }
);

$app->run();
