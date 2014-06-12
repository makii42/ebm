<?php
/**
 * This is a reimplementation of proxy.php that uses SILEX to proxy Jenkins reads and
 * also allows a more grained auth configuration.
 */

use Igorw\Silex\ConfigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

$app = new Silex\Application();
$env = getenv('APP_ENV') ? : 'prod';

$app->register(new ConfigServiceProvider(__DIR__ . "/../config/$env.json"));


$app->get(
    '/status/{hostLabel}/{jobName}',
    function ($hostLabel, $jobName) use ($app) {

        $hosts = $app['hosts'];

        if (!array_key_exists($hostLabel, $hosts)) {
            $app->abort(404, 'That host is not real, dude!');
        }
        $host = $hosts[$hostLabel];
        $jobs = $app['jobs'];
        if(!array_key_exists($hostLabel, $jobs)) {
            $app->abort(404, 'No jobs for this host, dude!');
        }
        $jobsByHost = $jobs[$hostLabel];

        return new JsonResponse($jobsByHost);
    }
);


$app->error(function (\Exception $e, $code) {
    $response = array('error' => $code);
    switch ($code) {
        case 404:
            $message = $e->getMessage();
            if(empty($message)) {
                $message = 'The requested page could not be found.';
            }
            $response['message'] = $message;
            break;
        default:
            $response['message'] = 'We are sorry, but something went terribly wrong.';
    }

    $jsonResponse = new JsonResponse($response);
    return $jsonResponse;
});

$app->run();
