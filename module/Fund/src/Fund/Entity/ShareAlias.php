<?php

namespace Fund\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ShareAlias
 *
 * @ORM\Table(
 *     name="share_alias",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="name",
 *             columns={"name"}
 *         ),
 *     }
 * )
 * @ORM\Entity
 */
class ShareAlias extends Entity
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name = '';

    /**
     * @var \Share
     *
     * @ORM\ManyToOne(targetEntity="\Fund\Entity\Share", inversedBy="share_alias")
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
     * Set name
     *
     * @param string $name
     * @return ShareAlias
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
     * Set share
     *
     * @param \Fund\Entity\Share $share
     * @return ShareAlias
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
