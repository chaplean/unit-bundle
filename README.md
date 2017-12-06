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

## 3. Import configuration

##### 3.1. Import unit file config in `config_test.yml`

```yaml
imports:
    - { resource: '@ChapleanUnitBundle/Resources/config/config.yml' }
```


##### 3.2. Configure mock (optional)

In `config_test.yml`
```yaml
chaplean_unit:
    mocked_service: <YourClassImplementingMockedServiceOnSetUpInterface>
```

Example class:
```php
class MockService implements MockedServiceOnSetUpInterface
{
    /**
     * @return void
     */
    public static function getMockedServices()
    {
        $knpPdf = \Mockery::mock('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator');
        $knpPdf->shouldReceive('getOutputFromHtml')->andReturn('example');
        $knpPdf->shouldReceive('getOutput')->andReturn('example');
       
        $mocks['knp_snappy.pdf'] = $knpPdf;
        
        $client = \Mockery::mock(Client::class);
        $client->shouldReceive('request')->andReturn(new Response());

        $mocks['guzzle.client.sor_api'] = $client;
        
        return $mocks;
    }
}
```

##### 3.3. Add parameter (optional)

Open `app/config/parameters*` files

Add and change the default value. The `false` value disable the loading of datafixtures.

```yaml
parameters:
    ...
    data_fixtures_namespace: App\Bundle\RestBundle\
```

# Role Provider

You can use phpunit's ```@dataProvider``` to automaticaly run a test with a
list of different values. We can use this to test a route against different
roles with a single unit test. To acheive this we will need to

1. list the roles and how to log as a user of that role
2. create a dataProvider giving for each role the expectations we want
(usually a http code)
3. write the test using the @dataProvider

## 1. Listing the roles

Add in your ```parameters_test.yml``` a ```test_roles``` dict as following:

```yaml
parameters:
    # Dictionnary where the key is the name of the role (displayed when a
    # failure happens), and the value is the reference to an entity used
    # to do the login (the entity is given to LogicalTestCase::authenticate()).
    test_roles:
        NotLogged: ''
        User: 'user-1'
        Admin: 'user-2'
```

## 2. Create a dataProvider

Add a provider in your test class:

```php
class ExampleTest extends FunctionalTestCase
{
    /**
     * @return array
     */
    public function rolesMustBeLoggedProvider()
    {
        return $this->rolesProvider(
            // rolesProvider is an utility to map your expectations with the
            // configured roles. It takes an array with the roles as keys and
            // your expectations as values.
            array(
                'NotLogged' => Response::HTTP_FORBIDDEN,
                'User'      => Response::HTTP_OK,
                'Admin'     => Response::HTTP_OK,
            )
        );
    }
    
    /**
     * @return array
     */
    public function rolesWithDifferentExpectations()
    {
        return $this->rolesProvider(
            // You can also give different expectations, see 3. Create a unittest
            // testWithDifferentExpectations to see how it translates in the test
            // function signature.
            array(
                'NotLogged' => Response::HTTP_FORBIDDEN,
                'User'      => array(Response::HTTP_OK),
                'Admin'     => array(Response::HTTP_OK, 'other expectation),
            )
        );
    }

    /**
     * @return array
     */
    public function rolesWithExtraRoles()
    {
        return $this->rolesProvider(
            array(
                'NotLogged' => Response::HTTP_FORBIDDEN,
                'User'      => Response::HTTP_OK,
                'Admin'     => Response::HTTP_OK,
            ),
            // You can also provide extra roles, thoses are added to the list
            // of default roles. Like with regular roles you provide the role
            // name as key and then the expectations as value, but the first
            // expectation must be the user to use to log in as.
            array(
                'SpecialCase' => array('user-3', Response::HTTP_OK)
            )
        );
    }
}
```

## 3. Create a unittest

Write unittests using the previous dataProvider

```php
class ExampleTest extends FunctionalTestCase
{
    // Data provider ommited, see previous section
    
    /**
     * @dataProvider rolesMustBeLoggedProvider
     * 
     * @param string  $user
     * @param integer $expectedCode
     *
     * @return void
     */
    public function testRouteMustBeLogged($user, $expectedCode)
    {
        $client = $this->createClientWith($user);
        $client->request('/protected/url');
        
        $response = $client->getResponse();
        $this->assertEquals($expectedCode, $response->getStatusCode());
    }
    
    /**
     * @dataProvider rolesWithDifferentExpectations
     * 
     * @param string  $client
     * @param integer $expectedCode
     * @param string  $otherExpectation
     *
     * @return void
     */
    public function testWithDifferentExpectations($client, $expectedCode, $otherExpectation = null)
    {
        // $otherExpectation is not defined for every value in the provider so we must default to null
    }
}
```

# Custom printer

If you want use a custom printer add `printerClass` attribute with `Chaplean\Bundle\UnitBundle\TextUI\ResultPrinter` value in `phpunit.xml`
```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        <!-- ... -->
         printerClass="Chaplean\Bundle\UnitBundle\TextUI\ResultPrinter"
>
```

[See an overview](https://asciinema.org/a/u4d6NsZAifpGRlMYhPjq5La6N)
