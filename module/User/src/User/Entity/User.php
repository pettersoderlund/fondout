<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\User as ZfcUser;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */

class User extends ZfcUser
{
    /*
    protected $org;

    public function setOrg($org)
    {
        $this->org = $org;
        return $this;
    }

    public function getOrg()
    {
        return $this->org;
    }
    */
}
