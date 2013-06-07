<?php

/**
 * This file is part of the pantarei/oauth2 package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pantarei\OAuth2\Tests\Entity;

use Doctrine\ORM\EntityRepository;
use Pantarei\OAuth2\Model\CodesManagerInterface;

/**
 * CodesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CodesRepository extends EntityRepository implements CodesManagerInterface
{
    public function loadByCode($code)
    {
        $result = $this->findOneBy(array(
            'code' => $code,
        ));
        if ($result !== null) {
            return $result;
        }

        return false;
    }
}
