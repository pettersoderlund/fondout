<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Emissions
 *
 * @ORM\Table(name="emissions")
 * @ORM\Entity
 */
class Emissions extends Entity
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
     * @var integer
     *
     * @ORM\Column(name="scope1", type="integer", nullable=true)
     */
    private $scope1;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope2", type="integer", nullable=true)
     */
    private $scope2;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope12", type="integer", nullable=true)
     */
    private $scope12;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope3", type="integer", nullable=true)
     */
    private $scope3;

    /**
     * @var \ShareCompanies
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="emissions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share_company", referencedColumnName="id")
     * })
     */
    private $shareCompany;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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

    public function getScope1()
    {
        return $this->scope1;
    }

    public function setScope1($scope1)
    {
        return $this->scope1 = $scope1;
    }

    public function getScope2()
    {
        return $this->scope2;
    }

    public function setScope2($scope2)
    {
        return $this->scope2 = $scope2;
    }

    public function getScope12()
    {
        return $this->scope12;
    }

    public function setScope12($scope12)
    {
        return $this->scope12 = $scope12;
    }

    public function getScope3()
    {
        return $this->scope3;
    }

    public function setScope3($scope3)
    {
        return $this->scope3 = $scope3;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->Date;
    }
}
