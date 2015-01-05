<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Share
 *
 * @ORM\Table(
 *     name="share",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="isin",
 *             columns={"isin"}
 *         ),
 *         @ORM\UniqueConstraint(name="nameIsin", columns={"isin", "name"})
 *     }
 * )
 * @ORM\Entity
 */
class Share extends Entity
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="isin", type="string", length=12, nullable=true)
     */
    protected $isin;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    protected $countryCode;

    /**
     * @var \ShareCompany
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="shares")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share_company", referencedColumnName="id")
     * })
     */
    protected $shareCompany;

    /**
     * @var \Fund\Entity\Shareholding[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Shareholding", mappedBy="share")
     **/
    protected $shareholdings = null;

    /**
    * @var \Fund\Entity\ShareCategory
    *
    * @ORM\ManyToOne(targetEntity="\Fund\Entity\FondoutCategory")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="category", referencedColumnName="id")
    * })
    */
    protected $category;


    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->shareholdings = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Share
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
     * @return Share
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
     * Set countryCode
     *
     * @param string $countryCode
     * @return Share
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Add shareholding
     *
     * @param \Fund\Entity\Shareholding $shareholding
     */
    public function addShareholding(\Fund\Entity\Shareholding $shareholding)
    {
        $this->shareholdings[] = $shareholding;
    }

    /**
     * Remove shareholdings
     *
     * @param \Fund\Entity\Shareholding $shareholdings
     */
    public function removeShareholding(\Fund\Entity\Shareholding $shareholdings)
    {
        $this->shareholdings->removeElement($shareholdings);
    }

    /**
     * Get shareholdings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShareholdings()
    {
        return $this->shareholdings;
    }

    /**
     * Set shareCompany
     *
     * @param \Fund\Entity\ShareCompany $shareCompany
     * @return Share
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

    /**
    * Gets the category.
    *
    * @return \Fund\Entity\ShareCategory
    */
    public function getCategory()
    {
      return $this->category ? $this->category : new ShareCategory();
    }

    /**
    * Sets the category.
    *
    * @param \Fund\Entity\ShareCategory $category the category
    *
    * @return self
    */
    public function setCategory(\Fund\Entity\ShareCategory $category)
    {
      $this->category = $category;
      
      return $this;
    }

}
