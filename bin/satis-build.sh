#!/usr/bin/env bash

# Argument 1 : package name

if [ $# -lt 1 ]; then
    echo 1>&2 "$0: not enough arguments"
    exit 2
elif [ $# -gt 1 ]; then
    echo 1>&2 "$0: too many arguments"
    exit 2
fi

ssh chaplean@satis.chaplean.com /home/www/chaplean.com/satis/bin/satis build \
    /home/www/chaplean.com/satis/satis.json \
    /home/www/chaplean.com/satis/web \
    $1
