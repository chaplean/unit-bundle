<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Fixtures\Loader;

/**
 * GeneratorData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class GeneratorData
{
    /**
     * @var array
     */
    private $entityDefinition;

    /**
     * @var integer
     */
    private $index;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Reference[]
     */
    private $references;

    /**
     * GeneratorData constructor.
     *
     * @param string $pathDefinition
     */
    public function __construct($pathDefinition)
    {
        $this->loader = new Loader();

        if (empty($this->entityDefinition)) {
            $this->entityDefinition = $this->loader->parseFile(empty($pathDefinition) ? __DIR__ . '/../Resources/config/datafixtures.yml' : $pathDefinition);
        }

        $this->index = 0;
        $this->references = array();
    }

    /**
     * @param string $class
     * @param string $fieldName
     *
     * @return void
     * @throws \Exception
     */
    public function classDefinitionExist($class, $fieldName)
    {
        if (empty($this->entityDefinition)) {
            throw new \Exception('No definition load !');
        } elseif (!isset($this->entityDefinition[$class])) {
            throw new \Exception('Missing defintion for entity (\'' . $class . '\')');
        } elseif (!isset($this->entityDefinition[$class]['properties'])) {
            throw new \Exception('Unvalid format in definition, \'properties\' not found');
        } elseif (!isset($this->entityDefinition[$class]['properties'][$fieldName])) {
            throw new \Exception('Missing definition for required field (\'' . $fieldName . '\')');
        }
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public function getFieldsDefined($class)
    {
        if (isset($this->entityDefinition[$class])) {
            return array_keys($this->entityDefinition[$class]['properties']);
        } else {
            return array();
        }
    }

    /**
     * @param string $class
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getData($class, $fieldName)
    {
        $this->classDefinitionExist($class, $fieldName);

        $property = $this->entityDefinition[$class]['properties'][$fieldName];
        $property = $this->parseProperty($property);

        return $this->loader->getValue(array($class => array('properties' => array($fieldName => $property))), $fieldName);
    }

    /**
     * @param string $class
     * @param string $fieldName
     *
     * @return string
     * @throws \Exception
     */
    public function getReference($class, $fieldName)
    {
        if (isset($this->references[$class . ':' . $fieldName])) {
            return $this->references[$class . ':' . $fieldName]->getReferenceKey();
        } else {
            throw new \Exception(sprintf('Reference \'%s\' not exist', $class . ':' . $fieldName));
        }
    }

    /**
     * @param string $class
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasReference($class, $fieldName)
    {
        try {
            $this->classDefinitionExist($class, $fieldName);
            $property = $this->entityDefinition[$class]['properties'][$fieldName];

            if (!isset($this->references[$class . ':' . $fieldName])) {
                $this->references[$class . ':' . $fieldName] = new Reference($property);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    protected function parseProperty($property)
    {
        if (strpos($property, '<current()>') !== false) {
            return str_replace('<current()>', $this->index++, $property);
        }

        return $property;
    }
}
