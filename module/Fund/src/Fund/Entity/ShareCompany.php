<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ShareCompanies
 *
 * @ORM\Table(
 *     name="share_company",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="id", columns={"id"}),
 *         @ORM\UniqueConstraint(name="name", columns={"name"})
 *     }
 * )
 * @ORM\Entity
 */
class ShareCompany extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var Share[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Share", mappedBy="shareCompany")
     **/
    protected $shares = null;

    /**
     * @var FundInstance[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Accusation", mappedBy="shareCompany")
     **/
    protected $accusations = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="market_value_sek", type="bigint", nullable=true)
     */
    private $marketValueSEK;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var \Fund\Entity\CarbonTracker
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\CarbonTracker", mappedBy="shareCompany")
     **/
    protected $carbonTracker = null;

    /**
     * @var \Fund\Entity\Emissions
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\Emissions", mappedBy="shareCompany")
     **/
    protected $emissions = null;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->accusations = new ArrayCollection();
        $this->shares = new ArrayCollection();
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
     * @return ShareCompany
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
     * Add shares
     *
     * @param \Fund\Entity\Share $shares
     * @return ShareCompany
     */
    public function addShare(\Fund\Entity\Share $shares)
    {
        $this->shares[] = $shares;

        return $this;
    }

    /**
     * Remove shares
     *
     * @param \Fund\Entity\Share $shares
     */
    public function removeShare(\Fund\Entity\Share $shares)
    {
        $this->shares->removeElement($shares);
    }

    /**
     * Get shares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * Add accusations
     *
     * @param \Fund\Entity\Accusation $accusations
     * @return ShareCompany
     */
    public function addAccusation(\Fund\Entity\Accusation $accusations)
    {
        $this->accusations[] = $accusations;

        return $this;
    }

    /**
     * Remove accusations
     *
     * @param \Fund\Entity\Accusation $accusations
     */
    public function removeAccusation(\Fund\Entity\Accusation $accusations)
    {
        $this->accusations->removeElement($accusations);
    }

    /**
     * Get accusations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccusations()
    {
        return $this->accusations;
    }

    /**
     * Get MarketValueSEK
     *
     * @return integer
     */
    public function getMarketValueSEK()
    {
        return $this->marketValueSEK;
    }

    /**
     * Set marketValueSEK
     *
     * @param int $marketValueSek
     * @return ShareCompany
     */
    public function setMarketValueSEK($marketValueSEK)
    {
        $this->marketValueSEK = $marketValueSEK;
        return $this;
    }

    /**
     * Add emissions
     *
     * @param \Fund\Entity\Emissions $emissions
     * @return ShareCompany
     */
    public function setEmissions(\Fund\Entity\Emissions $emissions)
    {
        $this->emissions = $emissions;

        return $this;
    }

    /**
     * Get emissions
     *
     * @return \Fund\Entity\Emissions
     */
    public function getEmissions()
    {
        return $this->emissions;
    }
}
