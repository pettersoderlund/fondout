<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Accusation
 *
 * @ORM\Table(
 *     name="share_company_accusation"
 * )
 * @ORM\Entity
 */
class Accusation extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="accusation", type="text", nullable=true)
     */
    private $accusation;

    /**
     * @var \AccusationCategory
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\AccusationCategory", inversedBy="accusations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accusation_category_id", referencedColumnName="id")
     * })
     */
    private $category;

    /**
     * @var \Source
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\Source", inversedBy="accusations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="source_id", referencedColumnName="id")
     * })
     */
    private $source;

    /**
     * @var \ShareCompanies
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\ShareCompany", inversedBy="accusations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="share_company_id", referencedColumnName="id")
     * })
     */
    private $shareCompany;

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
     * Set accusation
     *
     * @param string $accusation
     * @return Accusation
     */
    public function setAccusation($accusation)
    {
        $this->accusation = $accusation;

        return $this;
    }

    /**
     * Get accusation
     *
     * @return string
     */
    public function getAccusation()
    {
        return $this->accusation;
    }

    /**
     * Set category
     *
     * @param AccusationCategory $category
     * @return Accusation
     */
    public function setCategory(AccusationCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return AccusationCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Gets the source.
     *
     * @return \Fund\Entity\Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the source.
     *
     * @param \Fund\Entity\Source $source the source
     *
     * @return self
     */
    public function setSource(\Fund\Entity\Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Set shareCompany
     *
     * @param \Fund\Entity\ShareCompany $shareCompany
     * @return Accusation
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
     * toString
     *
     * @return string the accusation
     */
    public function __toString()
    {
        return $this->getAccusation();
    }
}
