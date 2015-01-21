<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Industry
 *
 * @ORM\Table(
 *     name="industry",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="name",
 *             columns={"name"}
 *         )
 *     },
 * )
 * @ORM\Entity
 */

class Industry extends Entity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
    * @var integer
    *
    * @ORM\Column(name="yahoo_id", type="integer", nullable=true)
    */
    protected $yahooId;

    /**
    * @var \Fund\Entity\Sector
    *
    * @ORM\ManyToOne(targetEntity="\Fund\Entity\Sector")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="sector", referencedColumnName="id")
    * })
    */
    protected $sector;

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
     * Set name
     *
     * @param string $name
     * @return Industry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
            return $this->name;
    }


    /**
    * Get yahoo id
    *
    * @return integer
    */
    public function getYahooId()
    {
      return $this->yahooId;
    }

    /**
    * Set yahoo id
    *
    * @param integer $yahooId
    * @return integer
    */
    public function setYahooId($yahooId)
    {
      $this->yahooId = $yahooId;

      return $this;
    }

    /**
    * Get sector
    *
    * @return \Fund\Entity\Sector
    */
    public function getSector()
    {
      return $this->sector;
    }

}
