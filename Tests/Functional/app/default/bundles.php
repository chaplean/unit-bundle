<?php

use Chaplean\Bundle\UnitBundle\ChapleanUnitBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\Bundle\TestBundle\TestBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

return [
    new SecurityBundle(),
    new FrameworkBundle(),
    new DoctrineBundle(),
    new ChapleanUnitBundle(),
    new TestBundle(),
];
