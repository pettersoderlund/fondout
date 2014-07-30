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
     * @var string
     *
     * @ORM\Column(name="mainCategory", type="string", length=255, nullable=true)
     */
    protected $mainCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="subCategory", type="string", length=255, nullable=true)
     */
    protected $subCategory;

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
     * Set mainCategory
     *
     * @param string $mainCategory
     * @return Fund
     */
    public function setMainCategory($mainCategory)
    {
        $this->mainCategory = $mainCategory;

        return $this;
    }

    /**
     * Get mainCategory
     *
     * @return string
     */
    public function getMainCategory()
    {
        return $this->mainCategory;
    }

        /**
     * Set subCategory
     *
     * @param string $subCategory
     * @return Fund
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return string
     */
    public function getSubCategory()
    {
        return $this->subCategory;
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
    public function removeFundInstance(\Fund\Entity\FundInstance $fundInstances)
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
        $this->sustainability = ($totalMarketValue - $controversialValue) / $totalMarketValue;

        return $this;
    }
}
