Getting Started With ChapleanUnitBundle
=======================================

# Prerequisites

This version of the bundle requires Symfony 2.7+.

# Installation

Installation step process:

1. Download ChapleanUnitBundle using composer
2. Add the Bundle and dependency
4. Configure your application's config_test.yml
5. Create datafixtures definition for auto-generate data required
6. Create FeatureContext class who extend ChapleanContext
7. Copy behat.yml.dist

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

### Step 3: Create FeatureContext class

#### Architecture

Add a folder:

``` bash
\- src
    \- <Your Bundle>
        \- Feature
            \- Context
                |- FeatureContext.php
            |- <your feature>.feature
```

#### Creation context

Create FeatureContext class:

``` php
<?php
// src/<Your bundle>/Features/Context/FeatureContext.php

namespace <Your bundle>\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

class FeatureContext extends ChapleanContext
{
    
}

```
### Step 4: Copy behat.yml.dist configuration

Create behat.yml from behat.yml.dist

## Syntax for Nelmio\Alice

### Example file datafixtures.yml

```yaml
Chaplean\Bundle\UnitBundle\Entity\Client:
    properties:
        name: <lastname()>
        code: R<current()>ABC
        email: client<current()>.<email()>
        dateAdd: <(new \DateTime('-2 hours'))>
```
More: [nelmio/alice](https://github.com/nelmio/alice):

# Let's go

### Run Selenium server

For run a Selenium server:

``` bash
$ java -jar bin/selenium-server-standalone-2.45.0.jar
```

If chrome doesn't work, add `-Dwebdriver.chrome.driver` option:

``` bash
$ java -jar bin/selenium-server-standalone-2.45.0.jar -Dwebdriver.chrome.driver="bin/chromedriver"
```

### Run Logical test (phpunit)

cf doc Unit test

### Run Functional test (behat)

cd doc Unit test

# Run unit test in Bundle !

change a `phpunit.xml.dist`

``` xml
<phpunit
    [...]
    bootstrap                   = "vendor/autoload.php" >

    [...]
    <php>
        <server name="KERNEL_DIR" value="vendor/chaplean/unit-bundle/Chaplean/Bundle/UnitBundle/app/" />
    </php>
    [...]
```