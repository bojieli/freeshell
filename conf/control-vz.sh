#!/bin/bash
# usage: ./control-vz.sh <action> <id> [<param>]

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
action=$1
id=$2

for act in start stop restart status destroy; do
    if [ "$act" = "$action" ]; then
        if [ "$action" = "destroy" ] && [ "$3" = "keephome" ]; then
            echo "Backing up /home..."
            mkdir -p /home/vz/backup/$id
            mv /home/vz/private/$id/home /home/vz/backup/$id/
        fi
        vzctl $action $id;
        if [ $action = "start" ] || [ $action = "restart" ]; then
            vzctl exec $id "mount -t tmpfs -o noexec,nosuid tmpfs /tmp/"
        fi
        exit 0
    fi
done
exit 1
