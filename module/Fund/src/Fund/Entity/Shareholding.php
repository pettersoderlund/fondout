<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shareholding
 *
 * @ORM\Table(
 *     name="shareholding",
 *     indexes={
 *         @ORM\Index(
 *             name="fund_instance",
 *             columns={"fund_instance"}
 *         ),
 *         @ORM\Index(
 *             name="share",
 *             columns={"share"}
 *         )
 *     }
 * )
 * @ORM\Entity
 */
class Shareholding extends Entity
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
     * @ORM\Column(name="market_value",  type="decimal", precision=15, scale=3, nullable=true)
     */
    protected $marketValue;

    /**
     * @var \Fund\Entity\FundInstance
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\FundInstance", inversedBy="shareholdings")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund_instance", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $fundInstance;

    /**
     * @var \Fund\Entity\Share
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\Share", inversedBy="shareholdings")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $share;

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
     * Set quantity
     *
     * @param string $quantity
     * @return Shareholding
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set interestRate
     *
     * @param string $interestRate
     * @return Shareholding
     */
    public function setInterestRate($interestRate)
    {
        $this->interestRate = $interestRate;

        return $this;
    }

    /**
     * Get interestRate
     *
     * @return string
     */
    public function getInterestRate()
    {
        return $this->interestRate;
    }

    /**
     * Set exchangeRate
     *
     * @param string $exchangeRate
     * @return Shareholding
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    /**
     * Get exchangeRate
     *
     * @return string
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * Set marketValue
     *
     * @param integer $marketValue
     * @return Shareholding
     */
    public function setMarketValue($marketValue)
    {
        $this->marketValue = $marketValue;

        return $this;
    }

    /**
     * Get marketValue
     *
     * @return integer
     */
    public function getMarketValue()
    {
        return $this->marketValue;
    }

    /**
     * Set unlisted
     *
     * @param string $unlisted
     * @return Shareholding
     */
    public function setUnlisted($unlisted)
    {
        $this->unlisted = $unlisted;

        return $this;
    }

    /**
     * Get unlisted
     *
     * @return string
     */
    public function getUnlisted()
    {
        return $this->unlisted;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return Shareholding
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set fundInstance
     *
     * @param \Fund\Entity\FundInstance $fundInstance
     * @return Shareholding
     */
    public function setFundInstance(\Fund\Entity\FundInstance $fundInstance = null)
    {
        $this->fundInstance = $fundInstance;

        return $this;
    }

    /**
     * Get fundInstance
     *
     * @return \Fund\Entity\FundInstance
     */
    public function getFundInstance()
    {
        return $this->fundInstance;
    }

    /**
     * Set share
     *
     * @param \Fund\Entity\Share $share
     * @return Shareholding
     */
    public function setShare(\Fund\Entity\Share $share = null)
    {
        $this->share = $share;

        return $this;
    }

    /**
     * Get share
     *
     * @return \Fund\Entity\Share
     */
    public function getShare()
    {
        return $this->share;
    }
}
