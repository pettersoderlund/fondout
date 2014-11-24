<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StockExchangeListing
 *
 * @ORM\Table(
 *     name="stock_exchange_listing",
 *     indexes={
 *         @ORM\Index(
 *             name="share_company",
 *             columns={"share_company"}
 *         ),
 *         @ORM\Index(
 *             name="stock_exhcange",
 *             columns={"stock_exchange"}
 *         )
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="stockexchange_listing", columns={"stock_exchange", "share_company"})
 *     }
 * )

 *
 * @ORM\Entity
 */
class StockExchangeListing extends Entity
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
     * @ORM\Column(name="symbol", type="string", length=255, nullable=false)
     */
    protected $symbol;

    /**
     * @var \Fund\Entity\ShareCompany
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\ShareCompany", inversedBy="StockExchangeListing")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share_company", referencedColumnName="id")
     * })
     */
    protected $shareCompany;

    /**
     * @var \Fund\Entity\StockExchange
     *
     * @ORM\ManyToOne(targetEntity="Fund\Entity\StockExchange", inversedBy="StockExchangeListing")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stock_exchange", referencedColumnName="id")
     * })
     */
    protected $stockExchange;

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
     * Set symbol
     *
     * @param string $symbol
     * @return BankFundListing
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set shareCompany
     *
     * @param \Fund\Entity\ShareCompany $shareCompany
     * @return StockExchangeListing
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
     * Set stockExchange
     *
     * @param \Fund\Entity\StockExchange $stockExchange
     * @return StockExchangeListing
     */
    public function setStockExchange(\Fund\Entity\StockExchange $stockExchange = null)
    {
        $this->stockExchange = $stockExchange;

        return $this;
    }

    /**
     * Get stockExchange
     *
     * @return \Fund\Entity\StockExchange
     */
    public function getStockExchange()
    {
        return $this->stockExchange;
    }
}
