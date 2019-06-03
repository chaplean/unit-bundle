<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;

/**
 * Reference.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
class Reference
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var integer
     */
    protected $min;

    /**
     * @var integer
     */
    protected $max;

    /**
     * @var array
     */
    protected $values;

    /**
     * Reference constructor.
     *
     * @param string $property
     *
     * @throws \Exception
     */
    public function __construct($property)
    {
        $this->index = 0;
        $this->buildReference($property);
    }

    /**
     * @param string $property
     *
     * @return void
     * @throws \Exception
     */
    private function buildReference($property)
    {
        $matches = null;
        preg_match('/@(.[^<>\[\]]*)(<\d*,*\s*\d*>|\[.*,?\])?/', $property, $matches);

        if (count($matches) >= 2) {
            if ($matches[0] == '@new()') {
                $this->key = null;
                return;
            }

            $this->key = $matches[1];
            if (isset($matches[2])) {
                $options = $matches[2];
                preg_match('/<(\d*),\s*(\d*)>/', $options, $matches);

                if (count($matches) == 3) {
                    // interval values
                    $this->type = 'interval';
                    $this->min = (int) $matches[1];
                    $this->index = $this->min;
                    $this->max = (int) $matches[2];
                } else {
                    preg_match('/\[(.+,?)\]/', $options, $matches);

                    if (count($matches) > 1) {
                        // array values
                        $this->type = 'array';

                        $array = preg_replace('/\s*,\s*/', ',', $matches[1]);
                        $this->values = explode(',', $array);
                    } else {
                        throw new InvalidDefinitionException(sprintf('Invalid definition reference \'%s\'', $property));
                    }
                }
            } else {
                $this->type = 'only';
            }
        }
    }

    /**
     * @return string
     */
    private function getReferenceArray()
    {
        $referenceKey = $this->key . $this->values[$this->index++];

        if ($this->index >= count($this->values)) {
            $this->index = 0;
        }

        return $referenceKey;
    }

    /**
     * @return string
     */
    private function getReferenceInterval()
    {
        $referenceKey = $this->key . $this->index++;

        if ($this->index > $this->max) {
            $this->index = $this->min;
        }

        return $referenceKey;
    }

    /**
     * @return string
     */
    public function getReferenceKey()
    {
        switch ($this->type) {
            case 'interval':
                return $this->getReferenceInterval();
            case 'array':
                return $this->getReferenceArray();
            case 'only':
            default:
                return $this->key;
        }
    }
}
