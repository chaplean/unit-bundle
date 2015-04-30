Getting Started With ChapleanUnitBundle
=======================================

# Prerequisites

This version of the bundle requires Symfony 2.6+.

# Installation

Installation step process:

1. Download ChapleanUnitBundle using composer
2. Add the Bundle and dependency
3. Create a BehatKernel class
4. Configure your application's config_test.yml
5. Create FeatureContext class
6. Copy behat.yml.dist

### Step 1: Download ChapleanUnitBundle using composer

Include ChapleanUnitBundle in `composer.json`

``` json
{
...
"require-dev": {
        "chaplean/unit-bundle": "1.1.0"
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
}
```

### Step 3: Create a BehatKernel class

#### BehatKernel

``` php
<?php
// app/BehatKernel.php

include 'AppKernel.php';

class BehatKernel extends AppKernel
{
    public function getLogDir()
    {
        return $this->rootDir.'/../var/logs/'.$this->environment;
    }

    public function getCacheDir()
    {
        return $this->rootDir.'/../var/cache/'.$this->environment;
    }
}

```

### Step 4: Configure your application's config_test.yml

Add paramter for liip logical test

``` yaml
# app/config/config_test.yml

liip_functional_test:
    cache_sqlite_db: true
```

### Step 5: Create FeatureContext class

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
### Step 6: Copy behat.yml.dist configuration

Create behat.yml

``` yaml
# ./behat.yml

default:
    suites:
        test_suite:
            type: symfony_bundle
            bundle: <Your bundle>
    extensions:
        Behat\Symfony2Extension:
            kernel:
                path: app/BehatKernel.php
                class: BehatKernel
        Behat\MinkExtension:
            base_url: <website url>
            browser_name: Firefox
            show_cmd: <command browser> %s
            sessions:
                default:
                    selenenium2:
                        browser: chrome
```

# Architecture file test

### Structure Behat test


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