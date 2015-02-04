<?php
/**
 * This is a reimplementation of proxy.php that uses SILEX to proxy Jenkins reads and
 * also allows a more grained auth configuration.
 */

use Ebm\Silex\EbmServiceProvider;
use Guzzle\GuzzleServiceProvider;
use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new ConfigServiceProvider(__DIR__ . "/../config/config.json"));
$app->register(new GuzzleServiceProvider());

// default logfile to parent dir for dev
// overwrite in config
$logFile = __DIR__ . "/../ebm.log";
if (isset($app['logFile'])) {
    $logFile = $app['logFile'];
}
$app->register(
    new MonologServiceProvider(),
    array(
        'monolog.logfile' => $logFile,
        'monolog.handler.debug' => true
    ));
$app->register(
    new TwigServiceProvider(),
    array('twig.path' => __DIR__ . '/../templates')
);

$app->register(new EbmServiceProvider());

$app->run();
