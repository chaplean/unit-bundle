<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * AbstractFixture.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
abstract class AbstractFixture extends BaseAbstractFixture
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var GeneratorData
     */
    private $generator;

    /**
     * @var array
     */
    private $matches;

    /**
     * Loads an Entity using stored reference
     * named by $name
     *
     * @param string        $name
     * @param ObjectManager $manager
     *
     * @return mixed
     */
    public function getEntity($name, $manager)
    {
        $entity = $this->referenceRepository->getReference($name);

        return $manager->find(get_class($entity), $entity->getId());
    }

    /**
     * Persist a entity with random data for
     * required field not set
     *
     * @param mixed         $entity
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function persist($entity, $manager = null)
    {
        $this->initGenerator($entity);
        $this->setManager($manager);

        $fieldsRequired = $this->getRequiredField($entity);

        foreach ($fieldsRequired as $field) {
            list($getter, $setter) = $this->getAccessor($field);

            if (!empty($entity->$getter())) {
                continue;
            }

            switch (true) {
                case $this->isEnum($field):
                    $value = $this->getEnum($this->matches[1][0]);
                    break;
                case isset($field['joinColumns']):
                    $value = $this->saveDependency($field['targetEntity']);
                    break;
                default:
                    $value = $this->generator->getData(get_class($entity), $field['fieldName']);
            }

            $entity->$setter($value);
        }

        $this->em->persist($entity);
    }

    /**
     * Save target entity dependency
     *
     * @param mixed $class
     *
     * @return mixed
     */
    public function saveDependency($class)
    {
        $dependency = new $class();

        $this->persist($dependency);
        $this->em->flush();

        return $dependency;
    }

    /**
     * @param array $field
     *
     * @return boolean
     */
    public function isEnum($field)
    {
        if (isset($field['columnDefinition'])) {
            preg_match_all('/enum\((.*)\)/i', $field['columnDefinition'], $this->matches);
            return !empty($this->matches) && count($this->matches) > 0;
        } else {
            return false;
        }
    }

    /**
     * Get a possible value for a enum
     *
     * @param string $enum
     *
     * @return string
     */
    public function getEnum($enum)
    {
        $possibleValues = explode(',', str_replace('\'', '', $enum));

        $index = rand(0, count($possibleValues) - 1);

        return $possibleValues[$index];
    }

    /**
     * Parse ORM annotations for get not nullable field
     *
     * @param mixed $entity
     *
     * @return array
     */
    public function getRequiredField($entity)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata(get_class($entity));

        $fieldMappings = $classMetadata->fieldMappings;
        $associationMappings = $classMetadata->associationMappings;

        $fieldsRequired = array_filter($fieldMappings, function ($field) {
            return !$field['nullable'] && !isset($field['id']);
        });
        $associationsRequired = array_filter($associationMappings, function ($entity) {
            return isset($entity['joinColumns']) && count(array_filter($entity['joinColumns'], function ($joinColumn) {
                return !$joinColumn['nullable'];
            }));
        });

        return $fieldsRequired + $associationsRequired;
    }

    /**
     * Get accessor for a attributes entity
     *
     * @param array $field
     *
     * @return array
     */
    public function getAccessor($field)
    {
        $fieldName = ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field['fieldName']))));
        $getter = 'get' . $fieldName;
        $setter = 'set' . $fieldName;

        return array($getter, $setter);
    }

    /**
     * Set manager used in datafixtures
     *
     * @param ObjectManager|null $manager
     *
     * @return void
     */
    public function setManager($manager)
    {
        if (empty($this->em) && !empty($manager)) {
            $this->em = $manager;
        }
    }

    /**
     * @param mixed $entity
     *
     * @return void
     */
    public function initGenerator($entity)
    {
        if (empty($this->generator)) {
            $reflectionClass = new \ReflectionClass(get_class($entity));
            $path = $reflectionClass->getFileName();
            $path = str_replace($reflectionClass->getShortName() . '.php', '', $path);

            $this->generator = new GeneratorData($path . '../Resources/config/datafixtures.yml');
        }
    }
}
