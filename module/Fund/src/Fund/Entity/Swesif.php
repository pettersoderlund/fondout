<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Swesif
 *
 * @ORM\Table(name="swesif")
 * @ORM\Entity
 */
class Swesif extends Entity
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
     * @ORM\Column(name="information", type="text", nullable=true)
     */
    protected $information;

    /**
     * @var bool
     *
     * @ORM\Column(name="opts_in", type="boolean", nullable=true)
     */
    protected $optsIn;

    /**
     * @var bool
     *
     * @ORM\Column(name="opts_out", type="boolean", nullable=true)
     */
    protected $optsOut;

    /**
     * @var bool
     *
     * @ORM\Column(name="affects", type="boolean", nullable=true)
     */
    protected $affects;

    /**
     * @var \Fund\Entity\Fund
     *
     * @ORM\OneToOne(targetEntity="\Fund\Entity\Fund", inversedBy="swesif")
     */
    protected $fund;

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
     * Set information
     *
     * @param string $information
     * @return Swesif
     */
    public function setInformation($information)
    {
        $this->information = $information;

        return $this;
    }

    /**
     * Get information
     *
     * @return string
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Has information
     *
     * @return bool
     */
    public function hasInformation()
    {
        return !empty($this->information);
    }

    /**
     * Set optsIn
     *
     * @param bool $optsIn
     * @return Swesif
     */
    public function setOptsIn($optsIn)
    {
        $this->optsIn = $optsIn;

        return $this;
    }

    /**
     * Get optsIn
     *
     * @return bool
     */
    public function getOptsIn()
    {
        return $this->optsIn;
    }

    /**
     * Set optsOut
     *
     * @param bool $optsOut
     * @return Swesif
     */
    public function setOptsOut($optsOut)
    {
        $this->optsOut = $optsOut;

        return $this;
    }

    /**
     * Get optsOut
     *
     * @return bool
     */
    public function getOptsOut()
    {
        return $this->optsOut;
    }

    /**
     * Set affects
     *
     * @param bool $affects
     * @return Swesif
     */
    public function setAffects($affects)
    {
        $this->affects = $affects;

        return $this;
    }

    /**
     * Get affects
     *
     * @return bool
     */
    public function getAffects()
    {
        return $this->affects;
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
}
