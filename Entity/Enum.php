<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 *
 * @ORM\Table(name="cl_type")
 * @ORM\Entity
 */
class Enum
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
     * @ORM\Column(name="enum_type", nullable=false, type="string", columnDefinition="enum('enum1','enum2','enum3') NOT NULL")
     */
    private $enumType;

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
     * Get enumType.
     *
     * @return mixed
     */
    public function getEnumType()
    {
        return $this->enumType;
    }

    /**
     * Set enumType.
     *
     * @param mixed $enumType
     *
     * @return self
     */
    public function setEnumType($enumType)
    {
        $this->enumType = $enumType;

        return $this;
    }
}
