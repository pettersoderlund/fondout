<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Fund
 *
 * @ORM\Table(
 *     name="funds",
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
     * @var Bank[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Bank", mappedBy="fund")
     **/
    protected $banks = null;

    /**
     * @var FundInstance[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\FundInstance", mappedBy="fund")
     **/
    protected $fundInstances = null;

    /**
     * @var \Fund\Entity\Swesif
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\Swesif", mappedBy="fund")
     */
    protected $swesif;

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
     * Get Swesif
     *
     * @return \Fund\Entity\Swesif
     */
    public function getSwesif()
    {
        if ($this->swesif == null) {
            return new \Fund\Entity\Swesif();
        }

        return $this->swesif;
    }

    /**
     * Create net asset value over time graph data
     *
     * @return array
     */
    public function createNavOverTimeGraphData()
    {
        $rows = array();

        foreach ($this->getFundInstances()->toArray() as $row) {
            $rows[] = array(
                'x' => (double) $row->getDate()->format('U'),
                'y' => (double) $row->getNetAssetValue()
            );
        }

        return $rows;
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
     * Set swesif
     *
     * @param \Fund\Entity\Swesif $swesif
     * @return Fund
     */
    public function setSwesif(\Fund\Entity\Swesif $swesif)
    {
        $this->swesif = $swesif;

        return $this;
    }

    /**
     * Add banks
     *
     * @param \Fund\Entity\Bank $banks
     * @return Fund
     */
    public function addBank(\Fund\Entity\Bank $banks)
    {
        $this->banks[] = $banks;

        return $this;
    }

    /**
     * Remove banks
     *
     * @param \Fund\Entity\Bank $banks
     */
    public function removeBank(\Fund\Entity\Bank $banks)
    {
        $this->banks->removeElement($banks);
    }

    /**
     * Get banks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBanks()
    {
        return $this->banks;
    }
}
