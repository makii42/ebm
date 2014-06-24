<?php
/**
 * This is a reimplementation of proxy.php that uses SILEX to proxy Jenkins reads and
 * also allows a more grained auth configuration.
 */

use Guzzle\GuzzleServiceProvider;
use Guzzle\Http\Client;
use Igorw\Silex\ConfigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

$app = new Silex\Application();
$env = getenv('APP_ENV') ? : 'prod';

$app->register(new ConfigServiceProvider(__DIR__ . "/../config/$env.json"));
$app->register(new GuzzleServiceProvider());

$logFile = __DIR__ . "/../$env.log";
if(isset($app['logFile']))
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

$app->get(
        '/',
        function () use ($app)
        {
            $monitorName = isset($app['monitor-name']) ? $app['monitor-name'] : "Easy Build Montior";
            return $app['twig']->render(
                    'index.twig',
                    array(
                            'monitorName' => $monitorName
                    ));
        });

$app->get(
        '/js/scripts',
        function () use ($app)
        {
            $app['monolog']->debug('looking for script files...');

            $scriptContent = array();
            $finder        = new Finder();
            $finder->files()->name('*js')->in(__DIR__ . '/../js');

            /** @var \SplFileInfo $file */
            foreach ($finder as $file)
            {
                $app['monolog']->debug('loading file ' . $file->getRealPath());
                $scriptContent[] = $file->getContents();
            }
            // config
            $scriptContent[] = "var configData = $.parseJSON('" . json_encode(
                            array(
                                    'monitor-name' => $app['monitor-name'],
                                    'jobs'         => $app['jobs'],
                                    'pageRefresh'  => $app['pageRefresh']
                            )) . "');";
            return new Response(implode($scriptContent), 200, array('Content-Type' => 'application/javascript'));
        });

$app->get(
        '/status/{hostLabel}/{jobName}',
        function ($hostLabel, $jobName) use ($app)
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

            $client   = new Client($host['url']);
            $basePath = isset($host['basePath']) ? $host['basePath'] : '/jenkins';
            $request  = $client->createRequest('GET', $basePath . '/job/' . $jobName . '/lastBuild/api/json?pretty=true');
            if (isset($host['auth']))
            {
                $request->setAuth($host['auth']['userName'], $host['auth']['password']);
            }
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
            }

            $jsonResponse = new JsonResponse($response);
            return $jsonResponse;
        });

$app->run();
