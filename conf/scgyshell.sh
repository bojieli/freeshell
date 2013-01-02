#!/bin/bash
# usage: scgyshell.sh <action> <params>

if [ -z $1 ]; then
    echo "Welcome to scgyshell!"
    exit 1;
fi
action=$1;

if [ -f ./${action}.sh ]; then
    ./${action}.sh $(echo $@ | awk '{$1="";print $0}')
else
    ./control-vz.sh $@
fi
