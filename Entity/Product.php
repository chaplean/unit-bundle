<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product.
 *
 * @package   Chaplean\Bundle\UnitBundle\Entity
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 *
 * @ORM\Table(name="cl_product")
 * @ORM\Entity
 */
class Product
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
     * @var Client
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="client")
     */
    private $client;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Provider", mappedBy="product")
     */
    private $prodiver;

    /**
     *
     */
    public function __construct()
    {
        $this->prodiver = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client.
     *
     * @param Client $client
     *
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
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
     * Get prodiver.
     *
     * @return ArrayCollection
     */
    public function getProdiver()
    {
        return $this->prodiver;
    }

    /**
     * Set prodiver.
     *
     * @param ArrayCollection $prodiver
     *
     * @return self
     */
    public function setProdiver($prodiver)
    {
        $this->prodiver = $prodiver;

        return $this;
    }
}
