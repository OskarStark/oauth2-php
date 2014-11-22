<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/config_dev.php';

require __DIR__.'/routing_test.php';
require __DIR__.'/security_test.php';

$app['session.test'] = true;