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
class Type
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="bigint_type", nullable=false, type="bigint")
     */
    private $bigintType;

    /**
     * @ORM\Column(name="integer_type", nullable=false, type="integer")
     */
    private $integerType;

    /**
     * @ORM\Column(name="smallint_type", nullable=false, type="smallint")
     */
    private $smallintType;

    /**
     * @ORM\Column(name="boolean_type", nullable=false, type="boolean")
     */
    private $booleanType;

    /**
     * @ORM\Column(name="date_type", nullable=false, type="date")
     */
    private $dateType;

    /**
     * @ORM\Column(name="datetime_type", nullable=false, type="datetime")
     */
    private $datetimeType;

    /**
     * @ORM\Column(name="decimal_type", nullable=false, type="decimal")
     */
    private $decimalType;

    /**
     * @ORM\Column(name="float_type", nullable=false, type="float")
     */
    private $floatType;

    /**
     * @ORM\Column(name="string_type", nullable=false, type="string")
     */
    private $stringType;

    /**
     * @ORM\Column(name="text_type", nullable=false, type="text")
     */
    private $textType;

    /**
     * @ORM\Column(name="array_type", nullable=false, type="array")
     */
    private $arrayType;

    /**
     * @ORM\Column(name="enum_type", nullable=false, type="string", columnDefinition="enum('enum1','enum2','enum3') NOT NULL")
     */
    private $enumType;

    /**
     * Get arrayType.
     *
     * @return mixed
     */
    public function getArrayType()
    {
        return $this->arrayType;
    }

    /**
     * Set arrayType.
     *
     * @param mixed $arrayType
     *
     * @return self
     */
    public function setArrayType($arrayType)
    {
        $this->arrayType = $arrayType;
        
        return $this;
    }

    /**
     * Get bigintType.
     *
     * @return mixed
     */
    public function getBigintType()
    {
        return $this->bigintType;
    }

    /**
     * Set bigintType.
     *
     * @param mixed $bigintType
     *
     * @return self
     */
    public function setBigintType($bigintType)
    {
        $this->bigintType = $bigintType;
        
        return $this;
    }

    /**
     * Get integerType.
     *
     * @return mixed
     */
    public function getIntegerType()
    {
        return $this->integerType;
    }

    /**
     * Set integerType.
     *
     * @param mixed $integerType
     *
     * @return self
     */
    public function setIntegerType($integerType)
    {
        $this->integerType = $integerType;
        
        return $this;
    }

    /**
     * Get smallintType.
     *
     * @return mixed
     */
    public function getSmallintType()
    {
        return $this->smallintType;
    }

    /**
     * Set smallintType.
     *
     * @param mixed $smallintType
     *
     * @return self
     */
    public function setSmallintType($smallintType)
    {
        $this->smallintType = $smallintType;
        
        return $this;
    }

    /**
     * Get booleanType.
     *
     * @return mixed
     */
    public function getBooleanType()
    {
        return $this->booleanType;
    }

    /**
     * Set booleanType.
     *
     * @param mixed $booleanType
     *
     * @return self
     */
    public function setBooleanType($booleanType)
    {
        $this->booleanType = $booleanType;
        
        return $this;
    }

    /**
     * Get dateType.
     *
     * @return mixed
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * Set dateType.
     *
     * @param mixed $dateType
     *
     * @return self
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
        
        return $this;
    }

    /**
     * Get datetimeType.
     *
     * @return mixed
     */
    public function getDatetimeType()
    {
        return $this->datetimeType;
    }

    /**
     * Set datetimeType.
     *
     * @param mixed $datetimeType
     *
     * @return self
     */
    public function setDatetimeType($datetimeType)
    {
        $this->datetimeType = $datetimeType;
        
        return $this;
    }

    /**
     * Get decimalType.
     *
     * @return mixed
     */
    public function getDecimalType()
    {
        return $this->decimalType;
    }

    /**
     * Set decimalType.
     *
     * @param mixed $decimalType
     *
     * @return self
     */
    public function setDecimalType($decimalType)
    {
        $this->decimalType = $decimalType;
        
        return $this;
    }

    /**
     * Get floatType.
     *
     * @return mixed
     */
    public function getFloatType()
    {
        return $this->floatType;
    }

    /**
     * Set floatType.
     *
     * @param mixed $floatType
     *
     * @return self
     */
    public function setFloatType($floatType)
    {
        $this->floatType = $floatType;
        
        return $this;
    }

    /**
     * Get stringType.
     *
     * @return mixed
     */
    public function getStringType()
    {
        return $this->stringType;
    }

    /**
     * Set stringType.
     *
     * @param mixed $stringType
     *
     * @return self
     */
    public function setStringType($stringType)
    {
        $this->stringType = $stringType;
        
        return $this;
    }

    /**
     * Get textType.
     *
     * @return mixed
     */
    public function getTextType()
    {
        return $this->textType;
    }

    /**
     * Set textType.
     *
     * @param mixed $textType
     *
     * @return self
     */
    public function setTextType($textType)
    {
        $this->textType = $textType;
        
        return $this;
    }

    /**
     * Get id.
     *
     * @return mixed
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
