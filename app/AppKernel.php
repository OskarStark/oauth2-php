<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

# Register Silex default providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\SerializerServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

# RememberMeServiceProvider depend on SecurityServiceProvider.
$app->register(new Silex\Provider\RememberMeServiceProvider());

# Register the service provider.
$app->register(new AuthBucket\OAuth2\Provider\AuthBucketOAuth2ServiceProvider());

# Register the test bundle provider.
$app->register(new AuthBucket\OAuth2\Tests\TestBundle\TestBundleServiceProvider());

require __DIR__.'/config/config_'.$app['env'].'.php';

$app->before(function (Request $request) use ($app) {
    $app['session']->start();
});
