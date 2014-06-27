<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SimilarFundScore
 *
 * @ORM\Entity
 */
class SimilarFundScore extends Entity
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
     * @ORM\Column(type="decimal")
     */
    protected $blacklistMarketValue = 0;

    /**
     * @ORM\Column(type="decimal")
     */
    protected $fundMarketValue = 0;

    /**
     * @ORM\OneToOne(targetEntity="\Fund\Entity\Fund")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    protected $fund;

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of blacklistMarketValue.
     *
     * @return mixed
     */
    public function getBlacklistMarketValue()
    {
        return $this->blacklistMarketValue;
    }

    /**
     * Gets the value of blacklistMarketValue.
     *
     * @return mixed
     */
    public function getFundMarketValue()
    {
        return $this->fundMarketValue;
    }

    /**
     * Gets the value of fund.
     *
     * @return mixed
     */
    public function getFund()
    {
        return $this->fund;
    }
}
