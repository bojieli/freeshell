#!/bin/bash
# usage: input <action> <params> in one line to stdin

# This script has no security check.
# Please ensure the input does not have injections.

if [ `whoami` != 'root' ]; then
    echo "This script must be run by root!"
    exit 1
fi

read action params;
if [ -z "$action" ]; then
    echo "Welcome to scgyshell!"
    exit 1
fi

if [ -f `dirname $0`/${action}.sh ]; then
    `dirname $0`/${action}.sh $params
elif [ "$action" == "vzlist" ]; then
    vzlist $params
else
    `dirname $0`/control-vz.sh $action $params
fi
