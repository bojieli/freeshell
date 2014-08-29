#!/bin/bash
# usage: ./copy-vz.sh <old-id> <new-node> <new-id> <new-storage>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ]; then
    exit 1
fi

VEID=$1
newnode=$2
newid=$3
newstorage=$4

VZ_CONF_ROOT=/etc/vz/conf
CONFFILE="$VZ_CONF_ROOT/$VEID.conf"
[ ! -f "$CONFFILE" ] && echo "VZ conf does not exist" && exit 1
source $CONFFILE
[ ! -d "$VE_PRIVATE" ] && echo "vz root dir $VE_PRIVATE does not exist" && exit 1
HOSTNAME=scgyshell-$newnode
NEW_VE_PRIVATE=$newstorage/private/$newid

SSH_PARAMS="-o StrictHostKeyChecking=no -o PasswordAuthentication=no"
if ! ssh $SSH_PARAMS $HOSTNAME ls $VZ_CONF_ROOT >/dev/null; then
    echo "$VZ_CONF_ROOT does not exist on remote system $HOSTNAME" && exit 1
fi
if ssh $SSH_PARAMS $HOSTNAME cat $VZ_CONF_ROOT/$newid.conf >/dev/null; then
    echo "WARNING: $VZ_CONF_ROOT/$newid.conf already exists on remote system $HOSTNAME"
fi

scp $SSH_PARAMS $CONFFILE $HOSTNAME:$VZ_CONF_ROOT/$newid.conf
ssh $SSH_PARAMS $HOSTNAME -- vzctl set $newid --private $NEW_VE_PRIVATE --save
ssh $SSH_PARAMS $HOSTNAME -- mkdir -p $NEW_VE_PRIVATE $VZ_MOUNT_ROOT/$newid

function do_sync(){
    rsync -a -e "ssh $SSH_PARAMS" $VE_PRIVATE/ $HOSTNAME:$NEW_VE_PRIVATE/
}

# initial sync
do_sync
# second pass sync to ensure consistency
if [ -n "$(vzctl status $oldid | grep running)" ]; then
    # leave it if chkpnt does not complete in 10 seconds and kill it if does not complete in 30 seconds
    timeout -k 30 10 vzctl chkpnt $oldid --suspend
    do_sync
    timeout -k 30 10 vzctl chkpnt $oldid --resume
fi

