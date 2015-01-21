AuthBucket\\OAuth2
==================

[![Build
Status](https://travis-ci.org/authbucket/oauth2-php.svg?branch=master)](https://travis-ci.org/authbucket/oauth2-php)
[![Coverage
Status](https://img.shields.io/coveralls/authbucket/oauth2-php.svg)](https://coveralls.io/r/authbucket/oauth2-php?branch=master)
[![Dependency
Status](https://www.versioneye.com/php/authbucket:oauth2-php/dev-master/badge.svg)](https://www.versioneye.com/php/authbucket:oauth2-php/dev-master)
[![Latest Stable
Version](https://poser.pugx.org/authbucket/oauth2-php/v/stable.svg)](https://packagist.org/packages/authbucket/oauth2-php)
[![Total
Downloads](https://poser.pugx.org/authbucket/oauth2-php/downloads.svg)](https://packagist.org/packages/authbucket/oauth2-php)
[![License](https://poser.pugx.org/authbucket/oauth2-php/license.svg)](https://packagist.org/packages/authbucket/oauth2-php)

The primary goal of
[AuthBucket\\OAuth2](http://oauth2-php.authbucket.com/) is to develop a
standards compliant [RFC6749
OAuth2.0](http://tools.ietf.org/html/rfc6749) library; secondary goal
would be develop corresponding wrapper [Symfony2
Bundle](http://symfony.com) and [Drupal module](https://www.drupal.org).

This library bundle with a [Silex](http://silex.sensiolabs.org/) based
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php)
for unit test and demo purpose. Installation and usage can refer as
below.

Installation
------------

Simply add a dependency on `authbucket/oauth2-php` to your project's
`composer.json` file if you use [Composer](http://getcomposer.org/) to
manage the dependencies of your project.

Here is a minimal example of a `composer.json`:

    {
        "require": {
            "authbucket/oauth2-php": "~3.0"
        }
    }

Demo
----

The demo is based on [Silex](http://silex.sensiolabs.org/) and
[AuthBucketOAuth2ServiceProvider](https://github.com/authbucket/oauth2-php/blob/master/src/Provider/AuthBucketOAuth2ServiceProvider.php).
Read though [Demo](http://oauth2-php.authbucket.com/demo) for more
information.

You may also run the demo locally. Open a console and execute the
following command to install the latest version in the `oauth2-php`
directory:

    $ composer create-project authbucket/oauth2-php oauth2-php "~3.0"

Then use the PHP built-in web server to run the demo application:

    $ cd oauth2-php
    $ ./app/console server:run

If you get the error
`There are no commands defined in the "server" namespace.`, then you are
probably using PHP 5.3. That's ok! But the built-in web server is only
available for PHP 5.4.0 or higher. If you have an older version of PHP
or if you prefer a traditional web server such as Apache or Nginx, read
the [Configuring a web
server](http://silex.sensiolabs.org/doc/web_servers.html) article.

Open your browser and access the <http://127.0.0.1:8000> URL to see the
Welcome page of demo application.

Also access <http://127.0.0.1:8000/demo/refresh_database> to initialize
the bundled SQLite database with user account `admin`:`secrete`.

Documentation
-------------

OAuth2's documentation is built with
[Sami](https://github.com/fabpot/Sami) and publicly hosted on [GitHub
Pages](http://authbucket.github.io/oauth2-php).

To built the documents locally, execute the following command:

    $ composer sami

Open `build/sami/index.html` with your browser for the documents.

Tests
-----

This project is coverage with [PHPUnit](http://phpunit.de/) test cases;
CI result can be found from [Travis
CI](https://travis-ci.org/authbucket/oauth2-php); code coverage report
can be found from
[Coveralls](https://coveralls.io/r/authbucket/oauth2-php).

To run the test suite locally, execute the following command:

    $ composer phpunit

Open `build/logs/html` with your browser for the coverage report.

References
----------

-   [RFC6749](http://tools.ietf.org/html/rfc6749)
-   [Demo](http://oauth2-php.authbucket.com/demo)
-   [API](http://authbucket.github.io/oauth2-php/)
-   [GitHub](https://github.com/authbucket/oauth2-php)
-   [Packagist](https://packagist.org/packages/authbucket/oauth2-php)
-   [Travis CI](https://travis-ci.org/authbucket/oauth2-php)
-   [Coveralls](https://coveralls.io/r/authbucket/oauth2-php)

License
-------

-   Code released under
    [MIT](https://github.com/authbucket/oauth2-php/blob/master/LICENSE)
-   Docs released under [CC BY
    4.0](http://creativecommons.org/licenses/by/4.0/)
