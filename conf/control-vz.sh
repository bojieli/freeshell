#!/bin/bash
# usage: ./control-vz.sh <action> <id> [<param>]

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
action=$1
VEID=$2
param=$3

CONFFILE="/etc/vz/conf/$VEID.conf"
[ ! -f "$CONFFILE" ] && echo "VZ conf does not exist" && exit 1
source $CONFFILE
[ ! -d "$VE_PRIVATE" ] && echo "vz root dir $VE_PRIVATE does not exist" && exit 1

for act in start stop force-stop reboot force-reboot status destroy; do
    if [ "$act" = "$action" ]; then
        if [ "$action" = "destroy" ]; then
            if [ ! -z "$param" ]; then
                BACKUP_DIR=$VE_PRIVATE/../../backup/$VEID
                rm -rf $BACKUP_DIR 2>/dev/null # clean directory
                mkdir -p $BACKUP_DIR
                echo "$param" > $BACKUP_DIR/backup-dirlist
                IFS="," read -ra dir <<< "$param"
                for d in "${dir[@]}"; do
                    if [ -e "$VE_PRIVATE/$d" ]; then
                        echo "Backing up $d ..."
                        mkdir -p $BACKUP_DIR/$(dirname $d) # make parent directory
                        mv $VE_PRIVATE/$d $BACKUP_DIR/$(dirname $d)/
                    else
                        echo "$d not exist, skipping backup"
                    fi
                done
            fi
        fi

        param="$action $VEID"
        [ $action = "force-stop" ] && param="stop $VEID --fast"
        [ $action = "reboot" ] && param="restart $VEID"
        [ $action = "force-reboot" ] && param="restart $VEID --fast"
        # set 3 minute timeout in case the freeshell is locked
        timeout 180 vzctl $param

        if [ $action = "start" ] || [ $action = "reboot" ] || [ $action = "force-reboot" ]; then
            vzctl exec $VEID "mount -t tmpfs -o noexec,nosuid tmpfs /tmp/"
        fi
        if [ $action = "destroy" ]; then # in case vzctl destroy failed
            rm -rf $VE_PRIVATE
            rm -f $CONFFILE
        fi
        exit 0
    fi
done
exit 1
