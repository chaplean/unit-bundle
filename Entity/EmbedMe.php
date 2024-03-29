<?php

namespace Chaplean\Bundle\UnitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmbbedMe.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     3.0.0
 *
 * @ORM\Embeddable
 */
class EmbedMe
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $code;

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
}
