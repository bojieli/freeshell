#!/bin/bash
# usage: ./control-vz.sh <action> <id>

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
action=$1
id=$2

for act in start stop restart status; do
    if [ $act = $action ]; then
        vzctl $action $id;
        exit 0
    fi
done
exit 1
