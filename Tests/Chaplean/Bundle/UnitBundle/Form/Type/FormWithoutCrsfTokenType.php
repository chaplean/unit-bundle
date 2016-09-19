<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FormWithoutCrsfTokenType.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Form\Type
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     X.Y.Z
 */
class FormWithoutCrsfTokenType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
