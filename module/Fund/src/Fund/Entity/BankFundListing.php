<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BankFundListing
 *
 * @ORM\Table(
 *     name="bank_fund_listing",
 *     indexes={
 *         @ORM\Index(
 *             name="fund",
 *             columns={"fund"}
 *         ),
 *         @ORM\Index(
 *             name="bank",
 *             columns={"bank"}
 *         )
 *     }
 * )
 * @ORM\Entity
 */
class BankFundListing extends Entity
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
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var \Fund\Entity\Fund
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\Fund", inversedBy="BankFundListing")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund", referencedColumnName="id")
     * })
     */
    protected $fund;

    /**
     * @var \Fund\Entity\Bank
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\Bank", inversedBy="BankFundListing")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bank", referencedColumnName="id")
     * })
     */
    protected $bank;

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
     * Set url
     *
     * @param string $url
     * @return BankFundListing
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
     * Set fund
     *
     * @param \Fund\Entity\Fund $fund
     * @return BankFundListing
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
     * Set bank
     *
     * @param \Fund\Entity\Bank $bank
     * @return BankFundListing
     */
    public function setBank(\Fund\Entity\Bank $bank = null)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return \Fund\Entity\Bank
     */
    public function getBank()
    {
        return $this->bank;
    }
}
