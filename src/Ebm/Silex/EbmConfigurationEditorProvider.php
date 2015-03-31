<?php
/**
 * Created by PhpStorm.
 * User: ralfischer
 * Date: 3/3/15
 * Time: 09:28
 */

namespace Ebm\Silex;


use Silex\Application;
use Silex\ServiceProviderInterface;

class EbmConfigurationEditorProvider implements ServiceProviderInterface{

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
        // TODO: Implement register() method.
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
        // TODO: Implement boot() method.
    }
}