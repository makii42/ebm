<?php


namespace Ebm\Silex;


use Ebm\ScriptAppender;
use Guzzle\Http\Client;
use Igorw\Silex\ConfigServiceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EbmServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        /*
         * Registers a before hook to load the right configuration, if any.
         * This is considered a hack by me, and should be replaced soonish.
         */
        $app->before(
            function (Request $request) use ($app) {
                $pathInfo = $request->getPathInfo();
                if ('/' !== $pathInfo || !empty($pathInfo)) {
                    $pathElements = explode('/', $pathInfo);
                    $screenName = $pathElements[1];
                    $screenConfigFile = $app['monitorDir']
                        . DIRECTORY_SEPARATOR . $screenName . '.json';
                    $app['monolog']->debug('testing config file ' . $screenConfigFile);

                    if (file_exists($screenConfigFile)) {
                        $app->register(new ConfigServiceProvider($screenConfigFile));
                        $app['monolog']->debug('loaded config file ' . $screenConfigFile);
                    }
                }
            }
        );


        $app->get(
            '/',
            function () use ($app)
            {
                $monitorFinder = new Finder();
                $monitorFinder
                    ->in($app['monitorDir'])
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
            }
        );

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
            }
        );

        $app->get(
            '/{screen}/js/scripts',
            function ($screen) use ($app)
            {
                $libFinder = new Finder();
                $libFinder->files()->name('*js')->in($_SERVER["DOCUMENT_ROOT"] . '/../js/lib')->sortByName();
                $jsFinder = new Finder();
                $jsFinder->files()->name('*js')->in($_SERVER["DOCUMENT_ROOT"] . '/../js')->depth(0);

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
            }
        );
    }


    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
    }
}