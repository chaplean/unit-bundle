<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Client.
 *
 * @package   Chaplean\Bundle\UnitBundle\Entity
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 *
 * @ORM\Table(name="cl_client")
 * @ORM\Entity
 */
class Client
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, name="name")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false, name="code")
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="string", length=255, nullable=true)
     */
    private $details;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="client")
     */
    private $product;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_private_member", type="boolean", nullable=false)
     */
    private $isPrivateMember;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="has_code", type="boolean", nullable=false)
     */
    private $hasCode;

    /**
     *
     */
    public function __construct()
    {
        $this->product = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get details.
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set details.
     *
     * @param string $details
     *
     * @return self
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get product.
     *
     * @return ArrayCollection
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product.
     *
     * @param ArrayCollection $product
     *
     * @return self
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get dateAdd.
     *
     * @return \DateTime
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * Set dateAdd.
     *
     * @param \DateTime $dateAdd
     *
     * @return self
     */
    public function setDateAdd(\DateTime $dateAdd)
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive.
     *
     * @param boolean $isActive
     *
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isPrivateMember.
     *
     * @return boolean
     */
    public function isIsPrivateMember()
    {
        return $this->isPrivateMember;
    }

    /**
     * Set isPrivateMember.
     *
     * @param boolean $isPrivateMember
     *
     * @return self
     */
    public function setIsPrivateMember($isPrivateMember)
    {
        $this->isPrivateMember = $isPrivateMember;

        return $this;
    }

    /**
     * Get hasCode.
     *
     * @return boolean
     */
    public function hasCode()
    {
        return $this->hasCode;
    }

    /**
     * Set hasCode.
     *
     * @param boolean $hasCode
     *
     * @return self
     */
    public function setHasCode($hasCode)
    {
        $this->hasCode = $hasCode;

        return $this;
    }
}
