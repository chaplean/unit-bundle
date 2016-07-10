<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Debug\Exception\ContextErrorException;

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
     * @var GeneratorDataUtility
     */
    private $generator;

    /**
     * @var array
     */
    private $embeddedClass;

    /**
     * @param object $entity
     *
     * @return mixed
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
        $entity = $this->getReference($name);

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

        $entity = $this->generateEntity($entity);

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
     * @param object $entity
     *
     * @return void
     */
    public function getEmbeddedClass($entity)
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
     * @param string $class
     *
     * @return array
     */
    public function getAccessor($field, $class)
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
        return array($getter, $setter);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @param string $name
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::getReference
     * @return object
     * @throws \Exception
     */
    public function getReference($name)
    {
        $reference = $this->referenceRepository->getReference($name);
        if ($reference->getId() == null) {
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

            $this->generator = new GeneratorDataUtility($path . '../Resources/config/datafixtures.yml');
        }
    }
}
