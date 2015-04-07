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
     * @var FundInstance[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\FundInstance", mappedBy="fund")
     **/
    protected $fundInstances = null;

    protected $sustainability = 1;

    protected $totalMarketValue = 0;

    protected $controversialValue;

    protected $co2;

    protected $co2Coverage;

    # Counts of the fund measurements.
    protected $weaponCompanies   = 0;
    protected $fossilCompanies   = 0;
    protected $alcoholCompanies  = 0;
    protected $tobaccoCompanies  = 0;
    protected $gamblingCompanies = 0;

    // This is supposed to be a constant.
    protected $co2CoverageLimit = 0.5;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->fundInstances = new ArrayCollection();
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
    * Gets the value of sustainability.
    *
    * @return mixed
    */
    public function getSustainability()
    {
      return $this->sustainability;
    }

    /**
     * Sets the total market value.
     *
     * @return self
     */
    public function setTotalMarketValue()
    {
        // NOTE: ugly hack, breaks if we have more then one fund instance.

        $this->totalMarketValue = current($this->getFundInstances()->toArray())->totalMarketValue;

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

    /**
     * Sets the value of sustainability.
     *
     * @param mixed $sustainability the sustainability
     *
     * @return self
     */
    public function calculateSustainability($controversialValue)
    {
        // NOTE: ugly hack, breaks if we have more then one fund instance.
        $totalMarketValue     = current($this->getFundInstances()->toArray())->totalMarketValue;
        $this->controversialValue = $controversialValue;
        $this->sustainability = ($totalMarketValue - $controversialValue) / $totalMarketValue;

        return $this;
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

    public function getCo2()
    {
      return $this->co2;
    }

    public function setCo2($co2)
    {
      $this->co2 = $co2;
      return $this;
    }

    public function getCo2Coverage()
    {
      return $this->co2Coverage;
    }

    public function setCo2Coverage($co2Coverage)
    {
      $this->co2Coverage = $co2Coverage;
      return $this;
    }

    public function getCo2CoverageLimit() {
      return $this->co2CoverageLimit;
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
