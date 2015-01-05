<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* ShareCategory
*
* @ORM\Table(
*     name="share_Category",
*     uniqueConstraints={
*         @ORM\UniqueConstraint(
*             name="name",
*             columns={"name"}
*         )
*     },
* )
* @ORM\Entity(repositoryClass="Fund\Repository\FundRepository")
*/

class ShareCategory extends Entity
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
