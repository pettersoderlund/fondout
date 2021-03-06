<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Fund
 *
 * @ORM\Table(
 *     name="fund",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="institution_number",
 *             columns={"institution_number"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="isin",
 *             columns={"isin"}
 *         )
 *     },
 *     indexes={
 *         @ORM\Index(name="company", columns={"company"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Fund\Repository\FundRepository")
 */

class Fund extends Entity
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
		 * @var integer
		 *
		 * @ORM\Column(name="ppm_id", type="integer", nullable=true)
		 */
		protected $ppmId;


    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="isin", type="string", length=20, nullable=true)
     */
    protected $isin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = 1;

    /**
    * @var boolean
    *
    * @ORM\Column(name="fof", type="boolean", nullable=false)
    */
    protected $fof = 0;

    /**
     * @var \Fund\Entity\FundCategory
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\FundCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category", referencedColumnName="id")
     * })
     */
    protected $category;

    /**
     * @var integer
     *
     * @ORM\Column(name="morningstar_rating", type="integer", nullable=true)
     */
    protected $morningstarRating;

    /**
     * @var \Fund\Entity\FondoutCategory
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\FondoutCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fondoutcategory", referencedColumnName="id")
     * })
     */
    protected $fondoutcategory;

    /**
     * @var \Fund\Entity\FundCompany
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\FundCompany", inversedBy="funds")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company", referencedColumnName="id")
     * })
     */
    protected $company;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=9999, nullable=true)
     */
    protected $info;

    /**
     * @ORM\ManyToMany(targetEntity="\Fund\Entity\Bank")
     * @ORM\JoinTable(name="bank_fund_listing",
     *      joinColumns={@ORM\JoinColumn(name="fund", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="bank", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     **/
    private $banks;

    /**
     * @var decimal
     *
     * @ORM\Column(name="nav", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $nav;

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
     * @var decimal
     *
     * @ORM\Column(name="annual_fee", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $annualFee;

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
    * @var decimal
    *
    * @ORM\Column(name="weapon_companies_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $weaponCompaniesPercent = 0;

    /**
    * @var decimal
    *
    * @ORM\Column(name="fossil_companies_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $fossilCompaniesPercent = 0;

    /**
    * @var decimal
    *
    * @ORM\Column(name="alcohol_companies_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $alcoholCompaniesPercent = 0;

    /**
    * @var decimal
    *
    * @ORM\Column(name="tobacco_companies_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $tobaccoCompaniesPercent = 0;

    /**
    * @var decimal
    *
    * @ORM\Column(name="gambling_companies_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $gamblingCompaniesPercent = 0;

    /**
    * @var decimal
    *
    * @ORM\Column(name="shp_percent", type="decimal", precision=8, scale=3, nullable=true, options={"default":"0"})
    */
    private $shpPercent = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pm_date", type="date", nullable=true)
     */
    private $pmDate;

		/**
		 * @var boolean
		 *
		 * @ORM\Column(name="swesif", type="boolean", nullable=false, options={"default":"0"})
		 */
		protected $swesif = 0;


    /**
     * @var FundInstance[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\FundInstance", mappedBy="fund")
     **/
    protected $fundInstances = null;

    protected $totalMarketValue = 0;

    protected $controversialValue;


    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->fundInstances = new ArrayCollection();
        $this->banks = new ArrayCollection();
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
     * @return Fund
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
     * Set morningstar rating
     *
     * @param integer $morningstarRating
     * @return Fund
     */
    public function setMorningstarRating($morningstarRating)
    {
        $this->morningstarRating = $morningstarRating;

        return $this;
    }

    /**
     * Get morningstarRating
     *
     * @return integer
     */
    public function getMorningstarRating()
    {
        return $this->morningstarRating;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Fund
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Fund
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
     * Set isin
     *
     * @param string $isin
     * @return Fund
     */
    public function setIsin($isin)
    {
        $this->isin = $isin;

        return $this;
    }

    /**
     * Get isin
     *
     * @return string
     */
    public function getIsin()
    {
        return $this->isin;
    }

    /**
     * Set company
     *
     * @param \Fund\Entity\FundCompany $company
     * @return Fund
     */
    public function setCompany(\Fund\Entity\FundCompany $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Fund\Entity\FundCompany
     */
    public function getCompany()
    {
        return $this->company;
    }

    public function getCompanyId()
    {
        return $this->company->id;
    }

    public function getCompanyName()
    {
        return $this->company->name;
    }

    /**
     * Add fund instance
     *
     * @param \Fund\Entity\FundInstance $fundInstance
     */
    public function addFundInstance(\Fund\Entity\FundInstance $fundInstance)
    {
        $this->fundInstances[] = $fundInstance;
    }

    /**
     * Get fundInstances
     *
     * @return \Fund\Entity\FundInstance[]
     */
    public function getFundInstances()
    {
        return $this->fundInstances;
    }

    /**
     * Remove fundInstances
     *
     * @param \Fund\Entity\FundInstance $fundInstances
     */
    public function removeFundInstance($fundInstances)
    {
        $this->fundInstances->removeElement($fundInstances);
    }

    /**
     * Sets the total market value.
     *
     * @return self
     */
    public function setTotalMarketValue()
    {
        // NOTE: ugly hack, breaks if we have more then one fund instance.
        $fund_instances = $this->getFundInstances()->toArray();
        $this->totalMarketValue = array_pop($fund_instances)->totalMarketValue;

        return $this;
    }

    /**
     * Gets the total market value of the fund.
     *
     * @return String
     */
    public function getTotalMarketValue()
    {
        return $this->totalMarketValue;
    }

    public function getControversialValue()
    {
        return $this->controversialValue;
    }

    /**
     * Gets the category.
     *
     * @return \Fund\Entity\FundCategory
     */
    public function getCategory()
    {
        return $this->category ? $this->category : new FundCategory();
    }

    /**
     * Sets the category.
     *
     * @param \Fund\Entity\FundCategory $category the category
     *
     * @return self
     */
    public function setCategory(\Fund\Entity\FundCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Gets the fondout category.
     *
     * @return \Fund\Entity\FundCategory
     */
    public function getFondoutCategory()
    {
        return $this->fondoutcategory ? $this->fondoutcategory : new FondoutCategory();
    }

    /**
     * Gets the fondout category title.
     *
     * @return string
     */
    public function getFondoutCategoryTitle()
    {
        return $this->getFondoutCategory()->getTitle();
    }

    /**
     * Gets the fondout category id.
     *
     * @return string
     */
    public function getFondoutCategoryId()
    {
        return $this->getFondoutCategory()->getId();
    }

    /**
     * Gets the fund company id.
     *
     * @return string
     */
    public function getFundCompanyId()
    {
        return $this->getCompany()->getId();
    }

    /**
     * Gets the fund company
     *
     * @return \Fund\Entity\FundCompany
     */
    public function getFundCompany()
    {
        return $this->getCompany();
    }

    /**
     * Sets the fondout category.
     *
     * @param \Fund\Entity\FundCategory $category the category
     *
     * @return self
     */
    public function setFondoutCategory(\Fund\Entity\FondoutCategory $category)
    {
        $this->fondoutcategory = $category;

        return $this;
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

    public function fillMeasurePercent($accusationCategoryId, $percent) {
        switch ($accusationCategoryId) {
            # Controversial weapons id = 1
            case 1:
                $this->setWeaponCompaniesPercent($percent);
                break;
            # Alcohol  id = 14
            case 14:
                $this->setAlcoholCompaniesPercent($percent);
                break;
            # Tobacco id = 15
            case 15:
                $this->setTobaccoCompaniesPercent($percent);
                break;
            # Gambling id = 16
            case 16:
                $this->setGamblingCompaniesPercent($percent);
                break;
            # Fossil fuels id = 11
            case 11:
                $this->setFossilCompaniesPercent($percent);
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

/*
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
  */

  public function getMeasureScore($measureType) {
    switch ($measureType) {
      case "weapon":
        $categoryPercent = $this->weaponCompaniesPercent;
        break;
      case "fossil":
        $categoryPercent = $this->fossilCompaniesPercent;
        break;
      case "alcohol":
        $categoryPercent = $this->alcoholCompaniesPercent;
        break;
      case "tobacco":
        $categoryPercent = $this->tobaccoCompaniesPercent;
        break;
      case "gambling":
        $categoryPercent = $this->gamblingCompaniesPercent;
        break;
      case "shp":
        $categoryPercent = $this->shpPercent;
        break;
    }

    // Categorypercent is between 0 and 1, convert to 0-100
    $categoryPercent = $categoryPercent*100;

    if ($categoryPercent == 0) {
      $score = 10;
    } elseif ($categoryPercent < 1) {
      $score = 9;
    } elseif ($categoryPercent < 2) {
      $score = 8;
    } elseif ($categoryPercent < 5) {
      $score = 7;
    } elseif ($categoryPercent < 8) {
      $score = 6;
    } elseif ($categoryPercent < 13) {
      $score = 5;
    } elseif ($categoryPercent < 21) {
      $score = 4;
    } elseif ($categoryPercent < 34) {
      $score = 3;
    } elseif ($categoryPercent < 55) {
      $score = 2;
    } else {
      $score = 1;
    }
    return $score;
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
     * Get banks
     *
     * @return ArrayCollection \Entity\Bank
     */
    public function getBanks()
    {
        return $this->banks;
    }

    /**
     * Get banks in array
     *
     * @return Array String
     */
    public function getBankArray()
    {
      $bankArray = array();
        foreach($this->banks as $bank) {
          array_push($bankArray, $bank->name);
        };
      return $bankArray;
    }

    public function getCurrentFundInstance()
    {
      $mostrecent = new \DateTime('2000-01-01');
      $fiCurrent = null;
      foreach($this->fundInstances as $fi) {
        if($mostrecent < $fi->date) {
          $mostrecent = $fi->date;
          $fiCurrent = $fi;
        }
      }
      return $fiCurrent;
    }

    private function navToPercent($oldNav)
    {
      $currNav = $this->nav;
      if (!is_null($oldNav) && !is_null($currNav)) {
        $ratio = $currNav/$oldNav;
        return !is_null($ratio) ? ($ratio-1)*100 : null;
      } else {
        return null;
      }
    }

    public function getNav()
    {
      return $this->nav;
    }

    public function setNav($nav)
    {
      $this->nav = $nav;
    }

    /**
     * Set annualFee
     *
     * @param string $annualFee
     * @return Fund
     */
    public function setAnnualFee($annualFee)
    {
        $this->annualFee = $annualFee;
        return $this;
    }

    /**
     * Get annualFee
     *
     * @return string
     */
    public function getAnnualFee()
    {
        return $this->annualFee;
    }

    /**
     * Set nav1year
     *
     * @param string $nav1year
     * @return Fund
     */
    public function setNav1year($nav1year)
    {
        $this->nav1year = $this->navToPercent($nav1year);

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
     * @return Fund
     */
    public function setNav3year($nav3year)
    {
        $this->nav3year = $this->navToPercent($nav3year);

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
     * @return Fund
     */
    public function setNav5year($nav5year)
    {
        $this->nav5year = $this->navToPercent($nav5year);

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
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    public function setPmDate($date)
    {
        $this->pmDate = $date;

        return $this;
    }

    /**
     * Get pmDate
     *
     * @return \DateTime
     */
    public function getPmDate()
    {
        return $this->pmDate;
    }


    public function setActive($active)
    {
      $this->active = $active;
      return $this;
    }

    public function getActive()
    {
      return $this->active;
    }

    public function setPercent1year($percent) {
      $this->nav1year = $percent;
    }

    public function setPercent3year($percent) {
      $this->nav3year = $percent;
    }

    public function setPercent5year($percent) {
      $this->nav5year = $percent;
    }

    public function resetMeasures() {

      /*$this->setPercent1year(0);
      $this->setPercent3year(0);
      $this->setPercent5year(0);*/
      $this->setWeaponCompanies(0);
      $this->setAlcoholCompanies(0);
      $this->setTobaccoCompanies(0);
      $this->setFossilCompanies(0);
      $this->setGamblingCompanies(0);
      $this->setWeaponCompaniesPercent(0);
      $this->setAlcoholCompaniesPercent(0);
      $this->setTobaccoCompaniesPercent(0);
      $this->setFossilCompaniesPercent(0);
      $this->setGamblingCompaniesPercent(0);
      $this->setShpPercent(0);
      //$this->setNav(null);
      //$this->setDate(null);
  }

  public function setWeaponCompaniesPercent($percent) {
      $this->weaponCompaniesPercent = $percent;
  }

  public function setAlcoholCompaniesPercent($percent) {
      $this->alcoholCompaniesPercent = $percent;
  }

  public function setTobaccoCompaniesPercent($percent) {
      $this->tobaccoCompaniesPercent = $percent;
  }

  public function setFossilCompaniesPercent($percent) {
    $this->fossilCompaniesPercent = $percent;
  }

  public function setGamblingCompaniesPercent($percent) {
      $this->gamblingCompaniesPercent = $percent;
  }

  /**
   * Get getGamblingCompaniesPercent
   *
   * @return string
   */
  public function getGamblingCompaniesPercent() {
      return $this->gamblingCompaniesPercent;
  }

  public function getWeaponCompaniesPercent() {
    return $this->weaponCompaniesPercent;
  }

  public function getFossilCompaniesPercent() {
    return $this->fossilCompaniesPercent;
  }

  public function getAlcoholCompaniesPercent() {
      return $this->alcoholCompaniesPercent;
  }

  public function getTobaccoCompaniesPercent() {
      return $this->tobaccoCompaniesPercent;
  }

  public function setShpPercent($percent) {
      $this->shpPercent = $percent;
  }

  public function getShpPercent() {
      return $this->shpPercent;
  }

	public function getSwesif() {
			return $this->swesif;
	}


}
