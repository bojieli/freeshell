#!/bin/bash
# usage: scgyshell.sh <action> <params>

if [ -z $1 ]; then
    echo "Welcome to scgyshell!"
    exit 1;
fi
action=$1;

if [ -f `dirname $0`/${action}.sh ]; then
    `dirname $0`/${action}.sh $(echo $@ | awk '{$1="";print $0}')
else
    `dirname $0`/control-vz.sh $@
fi
