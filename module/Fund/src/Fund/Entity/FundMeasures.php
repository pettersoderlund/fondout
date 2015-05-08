<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FundMeasures
 *
 * @ORM\Table(name="fund_measures")
 * @ORM\Entity
 */
class FundMeasures extends Entity
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
     * @ORM\Column(name="nav1year", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $nav1year;

    /**
     * @var decimal
     *
     * @ORM\Column(name="nav3year", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $nav3year;

    /**
     * @var decimal
     *
     * @ORM\Column(name="nav5year", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $nav5year;

    /**
    * @var integer
    *
    * @ORM\Column(name="weapon_companies", type="integer", nullable=true, options={"default":"0"})
    */
    private $weaponCompanies = 0;

    /**
    * @var integer
    *
    * @ORM\Column(name="fossil_companies", type="integer", nullable=true, options={"default":"0"})
    */
    private $fossilCompanies = 0;

    /**
    * @var integer
    *
    * @ORM\Column(name="alcohol_companies", type="integer", nullable=true, options={"default":"0"})
    */
    private $alcoholCompanies = 0;

    /**
    * @var integer
    *
    * @ORM\Column(name="tobacco_companies", type="integer", nullable=true, options={"default":"0"})
    */
    private $tobaccoCompanies = 0;

    /**
    * @var integer
    *
    * @ORM\Column(name="gambling_companies", type="integer", nullable=true, options={"default":"0"})
    */
    private $gamblingCompanies = 0;

    /**
     * @var \Fund
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\Fund", inversedBy="measures")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund", referencedColumnName="id")
     * })
     */
    private $fund;

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
     * Set nav1year
     *
     * @param string $nav1year
     * @return FundMeasures
     */
    public function setNav1year($nav1year)
    {
        $this->nav1year = $nav1year;

        return $this;
    }

    /**
     * Get nav1year
     *
     * @return string
     */
    public function getNav1year()
    {
        return $this->nav1year;
    }

    /**
     * Set nav3year
     *
     * @param string $nav3year
     * @return FundMeasures
     */
    public function setNav3year($nav3year)
    {
        $this->nav3year = $nav3year;

        return $this;
    }

    /**
     * Get nav3year
     *
     * @return string
     */
    public function getNav3year()
    {
        return $this->nav3year;
    }

    /**
     * Set nav5year
     *
     * @param string $nav5year
     * @return FundMeasures
     */
    public function setNav5year($nav5year)
    {
        $this->nav5year = $nav5year;

        return $this;
    }

    /**
     * Get nav5year
     *
     * @return string
     */
    public function getNav5year()
    {
        return $this->nav5year;
    }


    /**
     * Set fund
     *
     * @param \Fund\Entity\Fund $fund
     * @return FundMeasures
     */
    public function setFund(\Fund\Entity\Fund $fund = null)
    {
        $this->fund = $fund;

        return $this;
    }

    /**
     * Get fund
     *
     * @return \Fund\Entity\Fund
     */
    public function getFund()
    {
        return $this->fund;
    }

    public function fillMeasure($accusationCategoryId, $count) {
        switch ($accusationCategoryId) {
            # Controversial weapons id = 1
            case 1:
                $this->setWeaponCompanies($count);
                break;
            # Alcohol  id = 14
            case 14:
                $this->setAlcoholCompanies($count);
                break;
            # Tobacco id = 15
            case 15:
                $this->setTobaccoCompanies($count);
                break;
            # Gambling id = 16
            case 16:
                $this->setGamblingCompanies($count);
                break;
            # Fossil fuels id = 11
            case 11:
                $this->setFossilCompanies($count);
                break;

            default:
                break;

            return $this;
        }
    }

    public function setWeaponCompanies($count) {
        $this->weaponCompanies = $count;
    }

    public function setAlcoholCompanies($count) {
        $this->alcoholCompanies = $count;
    }

    public function getAlcoholCompanies() {
        return $this->alcoholCompanies;
    }

    public function setTobaccoCompanies($count) {
        $this->tobaccoCompanies = $count;
    }

    public function getTobaccoCompanies() {
        return $this->tobaccoCompanies;
    }

    public function setFossilCompanies($count) {
      $this->fossilCompanies = $count;
    }

    public function setGamblingCompanies($count) {
        $this->gamblingCompanies = $count;
    }

    public function getGamblingCompanies() {
        return $this->gamblingCompanies;
    }

    public function getWeaponCompanies() {
      return $this->weaponCompanies;
    }

    public function getFossilCompanies() {
      return $this->fossilCompanies;
    }

    public function getMeasureScore($measureType) {
      switch ($measureType) {
        case "weapon":
          $companyCount = $this->weaponCompanies;
          break;
        case "fossil":
          $companyCount = $this->fossilCompanies;
          break;
        case "alcohol":
          $companyCount = $this->alcoholCompanies;
          break;
        case "tobacco":
          $companyCount = $this->tobaccoCompanies;
          break;
        case "gambling":
          $companyCount = $this->gamblingCompanies;
          break;

      }

      if ($companyCount == 0) {
        $score = 10;
      } elseif ($companyCount == 1) {
        $score = 9;
      } elseif ($companyCount == 2) {
        $score = 8;
      } elseif ($companyCount < 5) {
        $score = 7;
      } elseif ($companyCount < 8) {
        $score = 6;
      } elseif ($companyCount < 13) {
        $score = 5;
      } elseif ($companyCount < 21) {
        $score = 4;
      } elseif ($companyCount < 34) {
        $score = 3;
      } elseif ($companyCount < 55) {
        $score = 2;
      } else {
        $score = 1;
      }
      return $score;
    }
}
