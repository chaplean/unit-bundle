<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Resources\Utility;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ServiceLambda.
 *
 * @package   App\Tests\Chaplean\Bundle\UnitBundle\Resources\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class ServiceLambda
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ServiceLambda constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return TranslatorInterface|null
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }
}
