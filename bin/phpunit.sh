#!/usr/bin/env bash

# Argument 1 : mysql service

if [ $# -lt 1 ]; then
  echo 1>&2 "$0: not enough arguments"
  exit 2
elif [ $# -gt 1 ]; then
  echo 1>&2 "$0: too many arguments"
  exit 2
fi

./bin/services-waiting.sh $1

phpunit --configuration ./phpunit_default.xml --coverage-clover build/logs/clover-default.xml
phpunit --configuration ./phpunit_sqlite.xml --coverage-clover build/logs/clover-sqlite.xml
