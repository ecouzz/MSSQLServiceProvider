<?php

/**
 * Created by PhpStorm.
 * User: Lucas Milin
 */

namespace Lmilin\Silex\MSSQLServiceProvider\Provider;

use Silex\ServiceProviderInterface;
use Silex\Application;
use MSSQLAbstractLayer\MSSQLConnector;


class MSSQLServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
        $app['db.default_options'] = array(
            'driver' => 'mssql',
            'dbname' => null,
            'host' => 'localhost',
            'port' => '1433',
            'user' => 'root',
            'password' => null,
            'charset' => 'UTF-8'
        );

        $app['dbs.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;
            if ($initialized) {
                return;
            }
            $initialized = true;
            if (!isset($app['dbs.options'])) {
                $app['dbs.options'] = array('default' => isset($app['db.options']) ? $app['db.options'] : array());
            }
            $tmp = $app['dbs.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['db.default_options'], $options);
                if (!isset($app['dbs.default'])) {
                    $app['dbs.default'] = $name;
                }
            }
            $app['dbs.options'] = $tmp;
        });

        $app['dbs'] = function ($app) {
            $app['dbs.options.initializer']();
            $dbs = new Application();
            foreach ($app['dbs.options'] as $name => $options) {
                $dbs[$name] = function ($dbs) use ($options) {
                    return new MSSQLConnector($options['host'], $options['dbname'], $options['user'], $options['password'], $options['driver']);
                };
            }
            return $dbs;
        };

        // shortcuts for the "first" DB
        $app['db'] = function ($app) {
            $dbs = $app['dbs'];
            return $dbs[$app['dbs.default']];
        };
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {

    }


}