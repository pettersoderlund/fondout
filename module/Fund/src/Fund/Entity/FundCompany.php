<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FundCompany
 *
 * @ORM\Table(
 *     name="fund_company",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="fcin",
 *             columns={"institution_number"}
 *         )
 *     }
 * )
 * @ORM\Entity
 */
class FundCompany extends Entity
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
     * @var integer
     *
     * @ORM\Column(name="institution_number", type="integer", nullable=false)
     */
    protected $institutionNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var Fund[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Fund", mappedBy="company")
     **/
    protected $funds = null;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->funds = new ArrayCollection();
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
     * Set institutionNumber
     *
     * @param integer $institutionNumber
     * @return FundCompany
     */
    public function setInstitutionNumber($institutionNumber)
    {
        $this->institutionNumber = $institutionNumber;

        return $this;
    }

    /**
     * Get institutionNumber
     *
     * @return integer
     */
    public function getInstitutionNumber()
    {
        return $this->institutionNumber;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return FundCompany
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

    /**
     * Add fund
     *
     * @param \Fund\Entity\Fund $fund
     */
    public function addFund(\Fund\Entity\Fund $fund)
    {
        $this->funds[] = $fund;
    }

    /**
     * Remove funds
     *
     * @param \Fund\Entity\Fund $funds
     */
    public function removeFund(\Fund\Entity\Fund $funds)
    {
        $this->funds->removeElement($funds);
    }

    /**
     * Get funds
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFunds()
    {
        return $this->funds;
    }
}
