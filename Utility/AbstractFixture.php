<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use MyProject\Proxies\__CG__\stdClass;

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
        $this->setManager($manager);

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

        $fieldsRequired += $associationsRequired;

        foreach ($fieldsRequired as $field) {
            $isEnum = false;
            $matches = null;

            $fieldName = ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field['fieldName']))));
            $getter = 'get' . $fieldName;
            $setter = 'set' . $fieldName;

            if (!empty($entity->$getter())) {
                continue;
            }

            if (isset($field['columnDefinition'])) {
                preg_match_all('/enum\((.*)\)/i', $field['columnDefinition'], $matches);
                $isEnum = !empty($matches) && count($matches) > 0;
            }

            switch (true) {
                case $isEnum:
                    $value = $this->getEnum($matches[1][0]);
                    break;
                case isset($field['joinColumns']):
                    $value = $this->saveDependency($field['targetEntity']);
                    break;
                default:
                    $value = FixtureUtility::generateRandomData($field['type']);
            }

            $entity->$setter($value);
        }

        $this->em->persist($entity);
    }

    /**
     * Save target entity dependency
     *
     * @param stdClass $class
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
     * Set manager used in datafixtures
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function setManager($manager)
    {
        if (empty($this->em) && !empty($manager)) {
            $this->em = $manager;
        }
    }
}
