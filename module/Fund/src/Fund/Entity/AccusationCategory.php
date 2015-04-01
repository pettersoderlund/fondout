<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Accusation
 *
 * @ORM\Table(
 *     name="accusation_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="name",
 *             columns={"name"}
 *         ),
 *     }
 *  )
 * @ORM\Entity
 */
class AccusationCategory extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var Accusation[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Accusation", mappedBy="category")
     **/
    protected $accusations = null;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->accusations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of accusations.
     *
     * @return Accusation[]
     */
    public function getAccusations()
    {
        return $this->accusations;
    }

    /**
     * Sets the value of accusations.
     *
     * @param Accusation[] $accusations the accusations
     *
     * @return self
     */
    public function setAccusations(array $accusations)
    {
        $this->accusations = $accusations;

        return $this;
    }

    /**
     * __toString
     *
     * @return string the name of the category
     */
    public function __toString()
    {
        return $this->getName();
    }
}
