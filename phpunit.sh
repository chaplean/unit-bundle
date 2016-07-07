#!/usr/bin/env bash

docker exec unit_bundle phpunit --configuration ./phpunit_default.xml --coverage-php build/logs/clover-default.xml
docker exec unit_bundle phpunit --configuration ./phpunit_sqlite.xml --coverage-php build/logs/clover-sqlite.xml
