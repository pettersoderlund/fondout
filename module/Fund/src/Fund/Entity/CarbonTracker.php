<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarbonTracker
 *
 * @ORM\Table(name="carbon_tracker")
 * @ORM\Entity
 */
class CarbonTracker extends Entity
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
     * @var decimal
     *
     * @ORM\Column(name="coal", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $coal;

    /**
     * @var decimal
     *
     * @ORM\Column(name="oil", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $oil;

    /**
     * @var decimal
     *
     * @ORM\Column(name="gas", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $gas;

    /**
     * @var \ShareCompanies
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="carbonTracker")
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

    public function getOil()
    {
        return $this->oil;
    }

    public function setOil($oil)
    {
        $this->oil = $oil;
        return $this;
    }

    public function getGas()
    {
        return $this->gas;
    }

    public function setGas($gas)
    {
        $this->gas = $gas;
        return $this;
    }

    public function getCoal()
    {
        return $this->coal;
    }

    public function setCoal($coal)
    {
        $this->coal = $coal;
        return $this;
    }

    public function getTotalgtco2()
    {
        return $this->totalgtco2;
    }

    public function setTotalgtco2($totalgtco2)
    {
        $this->totalgtco2 = $totalgtco2;
        return $this;
    }
}
