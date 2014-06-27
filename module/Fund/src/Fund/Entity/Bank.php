<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Banks
 *
 * @ORM\Table(name="banks", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="fund", columns={"fund"})})
 * @ORM\Entity
 */
class Bank extends Entity
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
     * @ORM\Column(name="bank", type="string", length=255, nullable=false)
     */
    private $bank;

    /**
     * @var string
     *
     * @ORM\Column(name="fundname", type="string", length=255, nullable=false)
     */
    private $fundname;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=2000, nullable=false)
     */
    private $url;

    /**
     * @var \Funds
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\Fund", inversedBy="banks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund", referencedColumnName="id")
     * })
     */
    private $fund;

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
     * Set bank
     *
     * @param string $bank
     * @return Banks
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set fundname
     *
     * @param string $fundname
     * @return Banks
     */
    public function setFundname($fundname)
    {
        $this->fundname = $fundname;

        return $this;
    }

    /**
     * Get fundname
     *
     * @return string
     */
    public function getFundname()
    {
        return $this->fundname;
    }

    /**
     * Set fund
     *
     * @param \Fund\Entity\Fund $fund
     * @return Banks
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
     * Set url
     *
     * @param string $url
     * @return Bank
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
}
