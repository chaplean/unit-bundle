Getting Started With ChapleanUnitBundle
=======================================

# Prerequisites

This version of the bundle requires Symfony 2.8+.

# Installation

## 1. Composer

```
composer require chaplean/unit-bundle
```

## 2. AppKernel.php

Add
```
    $bundles[] = new Chaplean\Bundle\UnitBundle\ChapleanUnitBundle();
    $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
    $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
```

## 3. Add parameter (optional)

Open `app/config/parameters*` files

Add and change the default value. The `false` value disable the loading of datafixtures.

```yaml
parameters:
    ...
    data_fixtures_namespace: App\Bundle\RestBundle\|false
```
