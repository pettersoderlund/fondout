<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ShareCompanies
 *
 * @ORM\Table(
 *     name="share_companies",
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
     * @ORM\OneToMany(targetEntity="\Fund\Entity\Blacklist", mappedBy="shareCompany")
     **/
    protected $blacklists = null;


    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->blacklists = new ArrayCollection();
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
     * Add blacklists
     *
     * @param \Fund\Entity\Blacklist $blacklists
     * @return ShareCompany
     */
    public function addBlacklist(\Fund\Entity\Blacklist $blacklists)
    {
        $this->blacklists[] = $blacklists;

        return $this;
    }

    /**
     * Remove blacklists
     *
     * @param \Fund\Entity\Blacklist $blacklists
     */
    public function removeBlacklist(\Fund\Entity\Blacklist $blacklists)
    {
        $this->blacklists->removeElement($blacklists);
    }

    /**
     * Get blacklists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlacklists()
    {
        return $this->blacklists;
    }
}
