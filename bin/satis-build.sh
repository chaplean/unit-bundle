#!/usr/bin/env bash

# Argument 1 : package name

if [ $# -lt 1 ]; then
    echo 1>&2 "$0: not enough arguments"
    exit 2
elif [ $# -gt 1 ]; then
    echo 1>&2 "$0: too many arguments"
    exit 2
fi

echo "Trying to connect to satis.chaplean.coop"

ssh chaplean@satis.chaplean.coop /home/www/chaplean.coop/satis/bin/satis build \
    /home/www/chaplean.coop/satis/satis.json \
    /home/www/chaplean.coop/satis/web \
    $1

echo "Satis build finished"
