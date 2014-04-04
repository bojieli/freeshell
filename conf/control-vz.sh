#!/bin/bash
# usage: ./control-vz.sh <action> <id> [<param>]

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
action=$1
id=$2
param=$3

for act in start stop restart status destroy; do
    if [ "$act" = "$action" ]; then
        if [ "$action" = "destroy" ]; then
            if [ ! -z "$param" ]; then
                rm -rf /home/vz/backup/$id 2>/dev/null # clean directory
                mkdir -p /home/vz/backup/$id
                echo "$param" > /home/vz/backup/$id/backup-dirlist
                IFS="," read -ra dir <<< "$param"
                for d in "${dir[@]}"; do
                    if [ -e "/home/vz/private/$id/$d" ]; then
                        echo "Backing up $d ..."
                        mkdir -p /home/vz/backup/$id/$(dirname $d)
                        mv /home/vz/private/$id/$d /home/vz/backup/$id/$(dirname $d)/
                    else
                        echo "$d not exist, skipping backup"
                    fi
                done
            fi
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
