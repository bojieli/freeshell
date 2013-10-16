#!/bin/bash
# usage: ./control-vz.sh <action> <id>

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
action=$1
id=$2

for act in start stop restart status destroy; do
    if [ "$act" = "$action" ]; then
        vzctl $action $id;
        if [ $action = "start" ] || [ $action = "restart" ]; then
            vzctl exec $id "mount -t tmpfs -o noexec,nosuid tmpfs /tmp/"
        fi
        exit 0
    fi
done
exit 1
