<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoice.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 *
 * @ORM\Entity
 * @ORM\Table(name="cl_invoice")
 */
class Invoice
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
     * @var Client
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="client")
     */
    private $client;

    /**
     * @var EmbedMe
     *
     * @ORM\Embedded(class="EmbedMe")
     */
    private $embed;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get embed.
     *
     * @return EmbedMe
     */
    public function getEmbed()
    {
        return $this->embed;
    }

    /**
     * Set embed.
     *
     * @param EmbedMe $embed
     *
     * @return self
     */
    public function setEmbed($embed)
    {
        $this->embed = $embed;

        return $this;
    }
}
