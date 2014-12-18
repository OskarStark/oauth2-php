<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\OAuth2\Tests\TestBundle;

use AuthBucket\OAuth2\Tests\TestBundle\Controller\AuthorizeController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\ClientController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\DefaultController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\DemoController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\OAuth2Controller;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\ResourceController;
use AuthBucket\OAuth2\Tests\TestBundle\Controller\ScopeController;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Test bundle provider.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class TestBundleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['authbucket_oauth2.tests.authorize_controller'] = $app->share(function () use ($app) {
            return new AuthorizeController(
                $app['validator'],
                $app['serializer'],
                $app['authbucket_oauth2.model_manager.factory']
            );
        });

        $app['authbucket_oauth2.tests.client_controller'] = $app->share(function () use ($app) {
            return new ClientController(
                $app['validator'],
                $app['serializer'],
                $app['authbucket_oauth2.model_manager.factory']
            );
        });

        $app['authbucket_oauth2.tests.scope_controller'] = $app->share(function () use ($app) {
            return new ScopeController(
                $app['validator'],
                $app['serializer'],
                $app['authbucket_oauth2.model_manager.factory']
            );
        });

        $app['authbucket_oauth2.tests.default_controller'] = $app->share(function () {
            return new DefaultController();
        });

        $app['authbucket_oauth2.tests.demo_controller'] = $app->share(function () {
            return new DemoController();
        });

        $app['authbucket_oauth2.tests.oauth2_controller'] = $app->share(function () {
            return new OAuth2Controller();
        });

        $app['authbucket_oauth2.tests.resource_controller'] = $app->share(function () {
            return new ResourceController();
        });
    }

    public function boot(Application $app)
    {
    }
}
