<?php
/**
 * This is a reimplementation of proxy.php that uses SILEX to proxy Jenkins reads and
 * also allows a more grained auth configuration.
 */

use Ebm\ScriptAppender;
use Guzzle\GuzzleServiceProvider;
use Guzzle\Http\Client;
use Igorw\Silex\ConfigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new ConfigServiceProvider(__DIR__ . "/../config/config.json"));
$app->register(new GuzzleServiceProvider());

// default logfile to parent dir for dev
// overwrite in config
$logFile = __DIR__ . "/../ebm.log";
if (isset($app['logFile']))
{
    $logFile = $app['logFile'];
}
$app->register(
        new Silex\Provider\MonologServiceProvider(),
        array(
                'monolog.logfile'       => $logFile,
                'monolog.handler.debug' => true
        ));
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__ . '/../templates'));

$app->before(
        function (Request $request) use ($app)
        {
            $pathInfo = $request->getPathInfo();
            if ('/' !== $pathInfo || !empty($pathInfo))
            {
                $pathElements = explode('/', $pathInfo);
                $screenName   = $pathElements[1];
                $configFile   = __DIR__ . '/../config/' . $screenName . '.json';
                $app['monolog']->debug('testing config file ' . $configFile);

                if (file_exists($configFile))
                {
                    $app->register(new ConfigServiceProvider($configFile));
                    $app['monolog']->debug('loaded config file ' . $configFile);
                }
            }
        });

$app->get(
        '/',
        function () use ($app)
        {
            $monitorFinder = new Finder();
            $monitorFinder
                    ->in('../config')
                    ->depth(0)
                    ->files()
                    ->name('*json')
                    ->notName('config.json');

            $monitors = array();

            /** @var $file \Symfony\Component\Finder\SplFileInfo */
            foreach ($monitorFinder as $file)
            {
                $monitors[] = array(
                        'slug' => $file->getBasename('.json')
                );
            }

            return $app['twig']->render(
                    'joblist.twig',
                    array(
                            'monitors' => $monitors
                    ));
        });

$app->get(
        '/{screen}',
        function ($screen) use ($app)
        {
            $monitorName = isset($app['monitor-name']) ? $app['monitor-name'] : "Easy Build Montior";
            return $app['twig']->render(
                    'index.twig',
                    array(
                            'monitorName' => $monitorName,
                            'screen'      => $screen
                    ));
        });

$app->get(
        '/{screen}/js/scripts',
        function ($screen) use ($app)
        {
            $libFinder = new Finder();
            $libFinder->files()->name('*js')->in(__DIR__ . '/../js/lib')->sortByName();
            $jsFinder = new Finder();
            $jsFinder->files()->name('*js')->in(__DIR__ . '/../js')->depth(0);

            $scriptAppender = new ScriptAppender($app['monolog'], array($libFinder, $jsFinder));

            $scriptBlob = $scriptAppender->getBlob() .
                    "var configData = $.parseJSON('" . json_encode(
                            array(
                                    'screen'       => $screen,
                                    'monitor-name' => $app['monitor-name'],
                                    'jobs'         => $app['jobs'],
                                    'pageRefresh'  => $app['pageRefresh']
                            )) . "');";

            $app['monolog']->debug('delivering blob: ' . mb_strlen($scriptBlob) . ' bytes');
            return new Response($scriptBlob, 200, array('Content-Type' => 'application/javascript'));
        });

$app->get(
        '/{screen}/status/{hostLabel}/{jobName}',
        function ($screen, $hostLabel, $jobName) use ($app)
        {
            $hosts = $app['hosts'];

            if (!array_key_exists($hostLabel, $hosts))
            {
                $app->abort(404, 'That host is not real, dude!');
            }
            $host = $hosts[$hostLabel];
            $jobs = $app['jobs'];
            if (!array_key_exists($hostLabel, $jobs))
            {
                $app->abort(404, 'No jobs for this host, dude!');
            }
            $jobsByHost = $jobs[$hostLabel];
            if (!in_array($jobName, $jobsByHost))
            {
                $app->abort(404, 'That job is not real, dude!');
            }

            $client = new Client($host['url']);

            $basePath = isset($host['basePath']) ? $host['basePath'] : '/jenkins';
            $path     = $basePath . '/job/' . $jobName . '/lastBuild/api/json?pretty=true';

            $app['monolog']->debug('collecting job data resource: ' . $host['url'] . $path);
            $request = $client->createRequest('GET', $path);
            if (isset($host['auth']))
            {
                $request->setAuth($host['auth']['userName'], $host['auth']['password']);
            }
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

            $request->getQuery()->set('pretty', 'true');
            $response = $client->send($request);

            return new Response($response->getBody(true), $response->getStatusCode());
        }
);

$app->error(
        function (\Exception $e, $code)
        {
            $response = array('error' => $code);
            switch ($code)
            {
                case 404:
                    $message = $e->getMessage();
                    if (empty($message))
                    {
                        $message = 'The requested page could not be found.';
                    }
                    $response['message'] = $message;
                    break;
                default:
                    $response['message'] = 'We are sorry, but something went terribly wrong.';
                    $response['details'] = $e->getMessage();
            }

            $jsonResponse = new JsonResponse($response);
            return $jsonResponse;
        });

$app->run();
