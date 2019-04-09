# Upgrading Guide

## From 7.x to 8.x

* Fix a major bug with doctrine and the symfony client
* Depreciated functions:
    * `createRestClient`: incompatibility with `AbstractFOSRestController`
    * `runCommand`: Prefer `CommandTester`
* BC: Add compatibility with PhpUnit8
* BC: Restrict symfony client creation before the first `getReference` in each test case

##### Migrate compatibility to PhpUnit8:
Make a Find/Replace of 
`(public|protected) (static\s)?function (setUp|setUpBeforeClass)\(\)[^:]`
by
`$1 $2function $3\(\): void\n`


##### Migrate client creation
**Warning**: All cases are not supported by this Regex.

Make a Find/Replace of 
`\{\n([0-9a-zA-Z\s$@*\\\/=\->\('\);\[\]>,\._]+)(\s{8}\$client = (\$this->|self::)(createClientWith|createClient)\((('|\$)[0-9a-z\-]*'?)?\);\n)`
by
`\{\n$2$1`