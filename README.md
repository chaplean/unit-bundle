Getting Started With ChapleanUnitBundle
=======================================

# Prerequisites

This version of the bundle requires Symfony 2.8+.

# Installation

Installation step process:

1. Download ChapleanUnitBundle using composer
1. Add the Bundle and dependency
1. (optional) Add parameter in parameters.yml.dist

### Step 1: Download ChapleanUnitBundle using composer

Include ChapleanUnitBundle in `composer.json`

``` json
{
...
"require-dev": {
        "chaplean/unit-bundle": "2.0.*"
        ...
        }
}
```

Composer will install the bundle to your project's `vendor/chaplean` directory.

### Step 2: Add the bundle and dependency

Add the bundle and dependency in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    // ...
    $bundles[] = new Chaplean\Bundle\UnitBundle\ChapleanUnitBundle();
    $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
    $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
}
```

### Step 3: Add parameter (optional)

Open `app/config/parameters*` files

Add and change the default value

```
    data_fixtures_namespace: App\Bundle\RestBundle\
```
