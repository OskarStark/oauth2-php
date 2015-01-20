<?php

/*
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\OAuth2\Tests\TestBundle\Controller;

use AuthBucket\OAuth2\Exception\InvalidScopeException;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\ORM\Tools\SchemaTool;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;

class DemoController
{
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('demo/index.html.twig');
    }

    public function refreshDatabaseAction(Request $request, Application $app)
    {
        $conn = $app['db'];
        $em = $app['doctrine.orm.entity_manager'];

        $params = $conn->getParams();
        $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);

        try {
            $conn->getSchemaManager()->dropDatabase($name);
            $conn->getSchemaManager()->createDatabase($name);
            $conn->close();
        } catch (\Exception $e) {
            return 1;
        }

        $classes = array();
        foreach ($app['authbucket_oauth2.model'] as $class) {
            $classes[] = $em->getClassMetadata($class);
        }

        PersistentObject::setObjectManager($em);
        $tool = new SchemaTool($em);
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);

        $loader = new Loader();
        $loader->loadFromDirectory(__DIR__.'/../DataFixtures/ORM');
        $executor->execute($loader->getFixtures());

        return $app->redirect($app['url_generator']->generate('index'));
    }

    public function loginAction(Request $request, Application $app)
    {
        $session = $request->getSession();

        $error = $app['security.last_error']($request);
        $_username = $session->get('_username');
        $_password = $session->get('_password');

        return $app['twig']->render('demo/login.html.twig', array(
            'error' => $error,
            '_username' => $_username,
            '_password' => $_password,
        ));
    }

    public function authorizeAction(Request $request, Application $app)
    {
        // We only handle non-authorized scope here.
        try {
            return $app['authbucket_oauth2.oauth2_controller']->authorizeAction($request);
        } catch (InvalidScopeException $exception) {
            $message = unserialize($exception->getMessage());
            if ($message['error_description'] !== 'The requested scope is invalid.') {
                throw $exception;
            }
        }

        // Fetch parameters, which already checked.
        $clientId = $request->query->get('client_id');
        $username = $app['security']->getToken()->getUser()->getUsername();
        $scope = preg_split('/\s+/', $request->query->get('scope', ''));

        // Create form.
        $form = $app['form.factory']->createBuilder('form')->getForm();
        $form->handleRequest($request);

        // Save authorized scope if submitted by POST.
        if ($form->isValid()) {
            $modelManagerFactory = $app['authbucket_oauth2.model_manager.factory'];
            $authorizeManager = $modelManagerFactory->getModelManager('authorize');

            // Update existing authorization if possible, else create new.
            $authorize = $authorizeManager->readModelOneBy(array(
                'clientId' => $clientId,
                'username' => $username,
            ));
            if ($authorize === null) {
                $class = $authorizeManager->getClassName();
                $authorize = new $class();
                $authorize->setClientId($clientId)
                    ->setUsername($username)
                    ->setScope((array) $scope);
                $authorize = $authorizeManager->createModel($authorize);
            } else {
                $authorize->setClientId($clientId)
                    ->setUsername($username)
                    ->setScope(array_merge((array) $authorize->getScope(), $scope));
                $authorizeManager->updateModel($authorize);
            }

            // Back to this path, with original GET parameters.
            return $app->redirect($request->getRequestUri());
        }

        // Display the form.
        $authorizationRequest = $request->query->all();

        return $app['twig']->render('demo/authorize.html.twig', array(
            'client_id' => $clientId,
            'username' => $username,
            'scopes' => $scope,
            'form' => $form->createView(),
            'authorization_request' => $authorizationRequest,
        ));
    }

    public function authorizeCodeAction(Request $request, Application $app)
    {
        $session = $request->getSession();

        $_username = $session->get('_username', substr(md5(uniqid(null, true)), 0, 8));
        $_password = $session->get('_password', substr(md5(uniqid(null, true)), 0, 8));

        $session->set('_username', $_username);
        $session->set('_password', $_password);

        $userManager = $app['authbucket_oauth2.model_manager.factory']->getModelManager('user');
        $user = $userManager->createUser()
            ->setUsername($_username)
            ->setPassword($_password)
            ->setRoles(array(
                'ROLE_USER',
            ));
        $userManager->updateUser($user);

        $parameters = array(
            'response_type' => 'code',
            'client_id' => 'authorization_code_grant',
            'redirect_uri' => $request->getUriForPath('/demo/response_type/code'),
            'scope' => 'demoscope1 demoscope2 demoscope3',
            'state' => $session->getId(),
        );

        $url = Request::create($request->getUriForPath('/demo/authorize'), 'GET', $parameters)->getUri();

        return $app->redirect($url);
    }

    public function authorizeTokenAction(Request $request, Application $app)
    {
        $session = $request->getSession();

        $_username = $session->get('_username', substr(md5(uniqid(null, true)), 0, 8));
        $_password = $session->get('_password', substr(md5(uniqid(null, true)), 0, 8));

        $session->set('_username', $_username);
        $session->set('_password', $_password);

        $userManager = $app['authbucket_oauth2.model_manager.factory']->getModelManager('user');
        $user = $userManager->createUser()
            ->setUsername($_username)
            ->setPassword($_password)
            ->setRoles(array(
                'ROLE_USER',
            ));
        $userManager->updateUser($user);

        $parameters = array(
            'response_type' => 'token',
            'client_id' => 'implicit_grant',
            'redirect_uri' => $request->getUriForPath('/demo/response_type/token'),
            'scope' => 'demoscope1 demoscope2 demoscope3',
            'state' => $session->getId(),
        );

        $url = Request::create($request->getUriForPath('/demo/authorize'), 'GET', $parameters)->getUri();

        return $app->redirect($url);
    }

    public function responseTypeCodeAction(Request $request, Application $app)
    {
        $authorizationResponse = $request->query->all();

        $tokenPath = $app['url_generator']->generate('demo_grant_type_authorization_code', array(
            'code' => $authorizationResponse['code'],
        ));

        return $app['twig']->render('demo/response_type/code.html.twig', array(
            'authorization_response' => $authorizationResponse,
            'token_path' => $tokenPath,
        ));
    }

    public function responseTypeTokenAction(Request $request, Application $app)
    {
        $accessTokenResponse = $request->query->all();

        $modelPath = $app['url_generator']->generate('demo_resource_type_model', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $debugPath = $app['url_generator']->generate('demo_resource_type_debug_endpoint', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));

        return $app['twig']->render('demo/response_type/token.html.twig', array(
            'access_token_response' => $accessTokenResponse,
            'model_path' => $modelPath,
            'debug_path' => $debugPath,
        ));
    }

    public function grantTypeAuthorizationCodeAction(Request $request, Application $app)
    {
        $parameters = array(
            'grant_type' => 'authorization_code',
            'code' => $request->query->get('code'),
            'redirect_uri' => $request->getUriForPath('/demo/response_type/code'),
            'client_id' => 'authorization_code_grant',
            'client_secret' => 'uoce8AeP',
        );
        $server = array();
        $client = new Client($app);
        $crawler = $client->request('POST', '/api/oauth2/token', $parameters, array(), $server);
        $accessTokenResponse = json_decode($client->getResponse()->getContent(), true);
        $accessTokenRequest = get_object_vars($client->getRequest());

        $modelPath = $app['url_generator']->generate('demo_resource_type_model', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $debugPath = $app['url_generator']->generate('demo_resource_type_debug_endpoint', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $refreshPath = $app['url_generator']->generate('demo_grant_type_refresh_token', array(
            'username' => 'authorization_code_grant',
            'password' => 'uoce8AeP',
            'refresh_token' => $accessTokenResponse['refresh_token'],
        ));

        return $app['twig']->render('demo/grant_type/authorization_code.html.twig', array(
            'access_token_response' => $accessTokenResponse,
            'access_token_request' => $accessTokenRequest,
            'model_path' => $modelPath,
            'debug_path' => $debugPath,
            'refresh_path' => $refreshPath,
        ));
    }

    public function grantTypePasswordAction(Request $request, Application $app)
    {
        $parameters = array(
            'grant_type' => 'password',
            'username' => 'demousername1',
            'password' => 'demopassword1',
            'scope' => 'demoscope1',
        );
        $server = array(
            'PHP_AUTH_USER' => 'resource_owner_password_credentials_grant',
            'PHP_AUTH_PW' => 'Eevahph6',
        );
        $client = new Client($app);
        $crawler = $client->request('POST', '/api/oauth2/token', $parameters, array(), $server);
        $accessTokenResponse = json_decode($client->getResponse()->getContent(), true);
        $accessTokenRequest = get_object_vars($client->getRequest());

        $modelPath = $app['url_generator']->generate('demo_resource_type_model', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $debugPath = $app['url_generator']->generate('demo_resource_type_debug_endpoint', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $refreshPath = $app['url_generator']->generate('demo_grant_type_refresh_token', array(
            'username' => 'resource_owner_password_credentials_grant',
            'password' => 'Eevahph6',
            'refresh_token' => $accessTokenResponse['refresh_token'],
        ));

        return $app['twig']->render('demo/grant_type/password.html.twig', array(
            'access_token_response' => $accessTokenResponse,
            'access_token_request' => $accessTokenRequest,
            'model_path' => $modelPath,
            'debug_path' => $debugPath,
            'refresh_path' => $refreshPath,
        ));
    }

    public function grantTypeClientCredentialsAction(Request $request, Application $app)
    {
        $parameters = array(
            'grant_type' => 'client_credentials',
            'scope' => 'demoscope1',
        );
        $server = array(
            'PHP_AUTH_USER' => 'client_credentials_grant',
            'PHP_AUTH_PW' => 'yib6aiFe',
        );
        $client = new Client($app);
        $crawler = $client->request('POST', '/api/oauth2/token', $parameters, array(), $server);
        $accessTokenResponse = json_decode($client->getResponse()->getContent(), true);
        $accessTokenRequest = get_object_vars($client->getRequest());

        $modelPath = $app['url_generator']->generate('demo_resource_type_model', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $debugPath = $app['url_generator']->generate('demo_resource_type_debug_endpoint', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $refreshPath = $app['url_generator']->generate('demo_grant_type_refresh_token', array(
            'username' => 'client_credentials_grant',
            'password' => 'yib6aiFe',
            'refresh_token' => $accessTokenResponse['refresh_token'],
        ));

        return $app['twig']->render('demo/grant_type/client_credentials.html.twig', array(
            'access_token_response' => $accessTokenResponse,
            'access_token_request' => $accessTokenRequest,
            'model_path' => $modelPath,
            'debug_path' => $debugPath,
            'refresh_path' => $refreshPath,
        ));
    }

    public function grantTypeRefreshTokenAction(Request $request, Application $app)
    {
        $parameters = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->query->get('refresh_token'),
        );
        $server = array(
            'PHP_AUTH_USER' => $request->query->get('username'),
            'PHP_AUTH_PW' => $request->query->get('password'),
        );
        $client = new Client($app);
        $crawler = $client->request('POST', '/api/oauth2/token', $parameters, array(), $server);
        $accessTokenResponse = json_decode($client->getResponse()->getContent(), true);
        $accessTokenRequest = get_object_vars($client->getRequest());

        $modelPath = $app['url_generator']->generate('demo_resource_type_model', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $debugPath = $app['url_generator']->generate('demo_resource_type_debug_endpoint', array(
            'access_token' => $accessTokenResponse['access_token'],
        ));
        $refreshPath = $app['url_generator']->generate('demo_grant_type_refresh_token', array(
            'username' => $request->query->get('username'),
            'password' => $request->query->get('password'),
            'refresh_token' => $accessTokenResponse['refresh_token'],
        ));

        return $app['twig']->render('demo/grant_type/refresh_token.html.twig', array(
            'access_token_response' => $accessTokenResponse,
            'access_token_request' => $accessTokenRequest,
            'model_path' => $modelPath,
            'debug_path' => $debugPath,
            'refresh_path' => $refreshPath,
        ));
    }

    public function resourceTypeModelAction(Request $request, Application $app)
    {
        $parameters = array();
        $server = array(
            'HTTP_Authorization' => implode(' ', array('Bearer', $request->query->get('access_token'))),
        );
        $client = new Client($app);
        $crawler = $client->request('GET', '/api/resource/model', $parameters, array(), $server);
        $resourceResponse = json_decode($client->getResponse()->getContent(), true);
        $resourceRequest = get_object_vars($client->getRequest());

        return $app['twig']->render('demo/resource_type/model.html.twig', array(
            'resource_response' => $resourceResponse,
            'resource_request' => $resourceRequest,
        ));
    }

    public function resourceTypeDebugEndpointAction(Request $request, Application $app)
    {
        $parameters = array();
        $server = array(
            'HTTP_Authorization' => implode(' ', array('Bearer', $request->query->get('access_token'))),
        );
        $client = new Client($app);
        $crawler = $client->request('GET', '/api/resource/debug_endpoint', $parameters, array(), $server);
        $resourceResponse = json_decode($client->getResponse()->getContent(), true);
        $resourceRequest = get_object_vars($client->getRequest());

        return $app['twig']->render('demo/resource_type/debug_endpoint.html.twig', array(
            'resource_response' => $resourceResponse,
            'resource_request' => $resourceRequest,
        ));
    }
}
