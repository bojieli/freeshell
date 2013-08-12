#!/bin/bash
# usage: input <action> <params> in one line to stdin

# This script has no security check.
# Please ensure the input does not have injections.

read action params;
if [ -z "$action" ]; then
    echo "Welcome to scgyshell!"
    exit 1;
fi

if [ -f `dirname $0`/${action}.sh ]; then
    `dirname $0`/${action}.sh $params
elif [ "$action" == "vzlist" ]; then
    sudo vzlist $params
else
    `dirname $0`/control-vz.sh $@
fi
