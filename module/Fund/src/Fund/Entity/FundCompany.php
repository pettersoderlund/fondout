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
     * @ORM\Column(name="institution_number", type="integer", nullable=true)
     */
    protected $institutionNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="premium", type="boolean", nullable=false)
     */
    protected $premium=false;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=999, nullable=true)
     */
    protected $info;

    /**
     * @var string
     *
     * @ORM\Column(name="cite", type="string", length=999, nullable=true)
     */
    protected $cite;

    /**
     * @var string
     *
     * @ORM\Column(name="section1", type="string", length=999, nullable=true)
     */
    protected $section1;

    /**
     * @var string
     *
     * @ORM\Column(name="section2", type="string", length=999, nullable=true)
     */
    protected $section2;

    /**
     * @var string
     *
     * @ORM\Column(name="section3", type="string", length=999, nullable=true)
     */
    protected $section3;

    /**
     * @var string
     *
     * @ORM\Column(name="bullets", type="string", length=999, nullable=true)
     */
    protected $bullets;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    protected $contact;

    /**
     * @var Fund[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Fund", mappedBy="company")
     **/
    protected $funds = null;


    /**
     * @ORM\ManyToMany(targetEntity="Organisation", inversedBy="fund_companies")
     * @ORM\JoinTable(name="organisation_member")
     **/
    private $organisations;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->funds = new ArrayCollection();
        $this->organisations = new ArrayCollection();
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

    public function getPremium()
    {
        return $this->premium;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * return the company name
     *
     * @return string the name
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function getCite()
    {
      return $this->cite;
    }

    public function getSection1()
    {
      return $this->section1;
    }

    public function getSection2()
    {
      return $this->section2;
    }

    public function getSection3()
    {
      return $this->section3;
    }

    public function getBullets()
    {
      return $this->bullets;
    }

    public function getContact()
    {
      return $this->contact;
    }

    /**
     * Get organisations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganisations()
    {
        return $this->organisations;
    }
}
