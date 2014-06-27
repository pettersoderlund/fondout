<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Blacklist
 *
 * @ORM\Table(name="blacklist")
 * @ORM\Entity
 */
class Blacklist extends Entity
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
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="accusation", type="text", nullable=true)
     */
    private $accusation;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="source_organization", type="string", length=255, nullable=true)
     */
    private $sourceOrganization;

    /**
     * @var string
     *
     * @ORM\Column(name="source_url", type="string", length=1024, nullable=true)
     */
    private $sourceUrl;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="release_date", type="date", nullable=false)
     */
    private $releaseDate;

    /**
     * @var \ShareCompanies
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="blacklists")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share_company", referencedColumnName="id")
     * })
     */
    private $shareCompany;

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
     * Set company
     *
     * @param string $company
     * @return Blacklist
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get accusation
     *
     * @return string
     */
    public function getAccusation()
    {
        return $this->accusation;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return Blacklist
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set sourceOrganization
     *
     * @param string $sourceOrganization
     * @return Blacklist
     */
    public function setSourceOrganization($sourceOrganization)
    {
        $this->sourceOrganization = $sourceOrganization;

        return $this;
    }

    /**
     * Get sourceOrganization
     *
     * @return string
     */
    public function getSourceOrganization()
    {
        return $this->sourceOrganization;
    }

    /**
     * Set sourceUrl
     *
     * @param string $sourceUrl
     * @return Blacklist
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * Get sourceUrl
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * Set releaseDate
     *
     * @param \DateTime $releaseDate
     * @return Blacklist
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return \DateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set accusation
     *
     * @param string $accusation
     * @return Blacklist
     */
    public function setAccusation($accusation)
    {
        $this->accusation = $accusation;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set shareCompany
     *
     * @param \Fund\Entity\ShareCompany $shareCompany
     * @return Blacklist
     */
    public function setShareCompany(\Fund\Entity\ShareCompany $shareCompany = null)
    {
        $this->shareCompany = $shareCompany;

        return $this;
    }

    /**
     * Get shareCompany
     *
     * @return \Fund\Entity\ShareCompany
     */
    public function getShareCompany()
    {
        return $this->shareCompany;
    }
}
