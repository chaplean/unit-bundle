<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * AbstractFixture.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
abstract class AbstractFixture extends BaseAbstractFixture
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var GeneratorDataUtility
     */
    private $generator = null;

    /**
     * @var array
     */
    private $embeddedClass;

    /**
     * @param object $entity
     *
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    public function generateEntity($entity)
    {
        if (empty($this->generator)) {
            try {
                $this->initGenerator($entity);
            } catch (\Exception $e) {
                return $entity;
            }
        }

        $this->getEmbeddedClass($entity);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata(get_class($entity));
        $fields = $this->generator->getFieldsDefined(get_class($entity));

        foreach ($fields as $field) {
            if ($classMetadata->hasAssociation($field)) {
                $fieldDefinition = $classMetadata->getAssociationMapping($field);
            } elseif (isset($classMetadata->embeddedClasses[$field])) {
                $fieldDefinition = $classMetadata->embeddedClasses[$field];
            } else {
                $fieldDefinition = $classMetadata->getFieldMapping($field);
            }

            if (isset($fieldDefinition['class'])) {
                $fieldDefinition['isEmbeddedField'] = true;
                $fieldDefinition['fieldName'] = $field;
                $fieldDefinition['type'] = '';
            }

            list($getter, $setter) = $this->getAccessor($fieldDefinition, $entity);
            if (!empty($entity->$getter()) || $entity->$getter() !== null) {
                continue;
            }

            switch (true) {
                case isset($fieldDefinition['joinColumns']):
                    if ($this->generator->hasReference(get_class($entity), $field)) {
                        $reference = $this->generator->getReference(get_class($entity), $field);
                        if ($reference !== null) {
                            $value = $this->getReference($reference);
                        } else {
                            $value = $this->saveDependency($fieldDefinition['targetEntity']);
                        }
                    } else {
                        $value = $this->saveDependency($fieldDefinition['targetEntity']);
                    }
                    break;
                case isset($fieldDefinition['isEmbeddedField']) && $fieldDefinition['isEmbeddedField']:
                    $value = $this->generateEntity(new $this->embeddedClass[$field]());
                    break;
                default:
                    $value = $this->generator->getData(get_class($entity), $field);
            }

            $entity->$setter($value);
        }

        return $entity;
    }

    /**
     * Persist a entity with random data for
     * required field not set
     *
     * @param mixed         $entity
     * @param ObjectManager $manager
     *
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function persist($entity, ObjectManager $manager = null): void
    {
        $this->setManager($manager);

        $entity = $this->generateEntity($entity);

        $this->em->persist($entity);
    }

    /**
     * Save target entity dependency
     *
     * @param mixed $class
     *
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function saveDependency($class)
    {
        $dependency = new $class();

        $this->persist($dependency);
        $this->em->flush();

        return $dependency;
    }

    /**
     * @param object $entity
     *
     * @return void
     */
    public function getEmbeddedClass($entity): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata(get_class($entity));

        $embeddedClasses = $classMetadata->embeddedClasses;

        foreach ($embeddedClasses as $key => $embeddedClass) {
            $this->embeddedClass[$key] = $embeddedClass['class'];
        }
    }

    /**
     * Get accessor for a attributes entity
     *
     * @param array  $field
     * @param object $class
     *
     * @return array
     */
    public function getAccessor(array $field, $class): array
    {
        $fieldName = ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field['fieldName']))));
        if ($field['type'] == 'boolean') {
            if (method_exists($class, $field['fieldName'])) {
                $getter = $field['fieldName'];
            } else {
                $getter = 'is' . $fieldName;
            }
        } else {
            $getter = 'get' . $fieldName;
        }
        $setter = 'set' . $fieldName;

        return [$getter, $setter];
    }

    /**
     * @see \Doctrine\Common\DataFixtures\ReferenceRepository::getReference
     * Loads an object using stored reference
     * named by $name
     *
     * @param string $name
     *
     * @return object
     * @throws \Exception
     */
    public function getReference($name)
    {
        $reference = parent::getReference($name);
        
        if ($reference->getId() === null) {
            throw new \Exception(sprintf('\'%s\' is not persisted !', $name));
        }

        return $reference;
    }

    /**
     * Set manager used in datafixtures
     *
     * @param ObjectManager|null $manager
     *
     * @return void
     */
    public function setManager(?ObjectManager $manager): void
    {
        if (empty($this->em) && $manager !== null) {
            $this->em = $manager;
        }
    }

    /**
     * @param mixed $entity
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function initGenerator($entity): void
    {
        if ($this->generator === null) {
            $reflectionClass = new \ReflectionClass(get_class($entity));
            $path = $reflectionClass->getFileName();
            $path = str_replace($reflectionClass->getShortName() . '.php', '', $path);

            $datafixturesPath = $path . '../Resources/config/datafixtures.yml';

            if (!file_exists($datafixturesPath)) {
                throw new \Exception();
            }

            $this->generator = new GeneratorDataUtility($datafixturesPath);
        }
    }
}
