<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClimatePositive
 *
 * @ORM\Table(name="climate_positive")
 * @ORM\Entity
 */
class ClimatePositive extends Entity
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
     * @var \ShareCompanies
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="climatePositive")
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
     * @return CDPScore
     */
    public function setCompany($company)
    {
        $this->company = $company;

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
