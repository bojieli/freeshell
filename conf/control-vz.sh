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
        # set 3 minute timeout in case the freeshell is locked
        timeout 180 vzctl $action $id;
        if [ $action = "start" ] || [ $action = "restart" ]; then
            vzctl exec $id "mount -t tmpfs -o noexec,nosuid tmpfs /tmp/"
        fi
        if [ $action = "destroy" ]; then # in case vzctl destroy failed
            rm -rf /home/vz/private/$id
            rm -f /etc/vz/conf/$id.conf
        fi
        exit 0
    fi
done
exit 1
