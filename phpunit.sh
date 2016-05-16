#!/usr/bin/env bash

phpunit --configuration ./phpunit_default.xml --coverage-php build/logs/clover-default.xml
phpunit --configuration ./phpunit_sqlite.xml --coverage-php build/logs/clover-sqlite.xml
