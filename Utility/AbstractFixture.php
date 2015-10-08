<?php
namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @param mixed         $entity
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function persist($entity, $manager)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $manager->getClassMetadata(get_class($entity));

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
            $fieldName = ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field['fieldName']))));
            $getter = 'get' . $fieldName;
            $setter = 'set' . $fieldName;

            if (!empty($entity->$getter())) {
                continue;
            }

            if (isset($field['joinColumns'])) {
                $dependency = new $field['targetEntity']();
                $this->persist($dependency, $manager);
                $manager->flush();

                $entity->$setter($dependency);
            } else {
                $value = self::generateRandomData($field['type']);
                $entity->$setter($value);
            }
        }

        $manager->persist($entity);
    }

    public static function generateRandomData($type)
    {
        switch ($type) {
            case 'array':
                return array();
            case 'bigint':
            case 'integer':
                return rand(0);
            case 'smallint':
                return rand(0, 1);
            case 'boolean':
                return (bool) rand(0, 1);
            case 'date':
            case 'datetime':
                return new \DateTime();
            case 'decimal':
            case 'float':
                return (float) rand(0)/getrandmax();
            case 'string':
            case 'text':
                return str_shuffle('azertyuiopmlkjhgfdsqwxcvbn');
        }
        return null;
    }
}
