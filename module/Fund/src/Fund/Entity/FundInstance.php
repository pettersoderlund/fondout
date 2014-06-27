<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FundInstance
 *
 * @ORM\Table(
 *     name="fund_instances",
 *     indexes={@ORM\Index(name="fund", columns={"fund"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="fund_instance", columns={"fund", "date"})}
 * )
 * @ORM\Entity
 */
class FundInstance extends Entity
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_market_value",  type="decimal", precision=30, scale=15, nullable=true)
     */
    protected $totalMarketValue;

    /**
     * @var integer
     *
     * @ORM\Column(name="capital",  type="decimal", precision=30, scale=15, nullable=true)
     */
    protected $capital;

    /**
     * @var string
     *
     * @ORM\Column(name="net_asset_value", type="decimal", precision=30, scale=15, nullable=true)
     */
    protected $netAssetValue;

    /**
     * @var \Fund\Entity\Fund
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\Fund", inversedBy="fundInstances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund", referencedColumnName="id")
     * })
     */
    protected $fund;

    /**
     * @var \Fund\Entity\Shareholding[]
     *
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Shareholding", mappedBy="fundInstance")
     **/
    protected $shareholdings = null;

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
     * Set date
     *
     * @param \DateTime $date
     * @return FundInstance
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

    /**
     * Set totalMarketValue
     *
     * @param integer $totalMarketValue
     * @return FundInstance
     */
    public function setTotalMarketValue($totalMarketValue)
    {
        $this->totalMarketValue = $totalMarketValue;

        return $this;
    }

    /**
     * Get totalMarketValue
     *
     * @return integer
     */
    public function getTotalMarketValue()
    {
        return $this->totalMarketValue;
    }

    /**
     * Set capital
     *
     * @param integer $capital
     * @return FundInstance
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return integer
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set netAssetValue
     *
     * @param string $netAssetValue
     * @return FundInstance
     */
    public function setNetAssetValue($netAssetValue)
    {
        $this->netAssetValue = $netAssetValue;

        return $this;
    }

    /**
     * Get netAssetValue
     *
     * @return string
     */
    public function getNetAssetValue()
    {
        return $this->netAssetValue;
    }

    /**
     * Set fund
     *
     * @param \Fund\Entity\Fund $fund
     * @return FundInstance
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

    /**
     * Add shareholding
     *
     * @param \Fund\Entity\Shareholding $shareholding
     */
    public function addShareholding(\Fund\Entity\Shareholding $shareholding)
    {
        $this->shareholdings[] = $shareholding;
    }

    public function jsonSerialize()
    {
        return array(
            $this->getDate()->format('Y-m-d')  => $this->getNetAssetValue()
        );
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
}
