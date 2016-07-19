<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class User.
 *
 * @package   Chaplean\Bundle\UnitBundle\Entity
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 *
 * @ORM\Table(name="cl_unit_user")
 * @ORM\Entity
 */
class User
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
     * @ORM\Column(type="string", length=255, nullable=false, name="email")
     */
    private $email;

    /**
     * @var Status
     *
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="user")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    private $status;

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
     * Get status.
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param Status $status
     *
     * @return self
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;

        return $this;
    }
}
