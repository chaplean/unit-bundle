# Upgrading Guide

## From 8.x to 9.x

* `nelmio/alice` dependency had been removed. You CAN'T use datafixtures.yml now.
* `liip/LiipFunctionalTestBundle` dependency had been removed too (not necessary to use this bundle).

* Deprecated:
    * Function `FunctionalTestCase::assertStatusCode`
* Removed:
    * Class `Client`
    * Class `RestClient`
    * Function `FunctionalTestCase::createRestClient`
    * Function `FunctionalTestCase::runCommand`: use `CommandTester`
    * Dead code (like database backup, reloading database...)
* New implementation:
    * `FunctionalTestCase::createCommandTester` now needs command name instead of command class.
* New configuration:
    * `data_fixtures_namespace` now in `chaplean_unit` config namespace.
* Breaking Changes:
    * You **DO NOT** have access anymore to container/entity manager directly when extending `FunctionalTestCase`. You have to `bootKernel` or `createClient` for that
    * `FunctionalTestCase::initializeContainer` has been deleted.
    * `Reference` now throws `InvalidDefinitionException` instead of `InvalidArgumentException`
    * Kernel is rebooted completly after each test (as it is in Symfony by default)
* Possible side effects:
    * `self` calls for protected/public functions and properties have been replaced by `static` calls in `FunctionalTestCase` may overwrite some of your variables.
    * `FunctionalTestCase::getDefaultFixturesNamespace` now use `App` namespace by default

## From 7.x to 8.x

* Fix a major bug with doctrine and the symfony client
* Deprecated functions:
    * `createRestClient`: incompatibility with `AbstractFOSRestController`
    * `runCommand`: prefer `CommandTester`
* BC: Add compatibility with PhpUnit8
* BC: Restrict symfony client creation before the first `getReference` in each test case

##### Migrate compatibility to PhpUnit8:
Make a Find/Replace of 
`(public|protected) (static\s)?function (setUp|setUpBeforeClass|tearDown)\(\)[^:]`
by
`$1 $2function $3\(\): void\n`


##### Migrate client creation
**Warning**: All cases are not supported by this Regex.

Make a Find/Replace of 
`\{\n([0-9a-zA-Z\s$@*\\\/=\->\('\);\[\]>,\._]+)(\s{8}\$client = (\$this->|self::)(createClientWith|createClient)\((('|\$)[0-9a-z\-]*'?)?\);\n)`
by
`\{\n$2$1`
