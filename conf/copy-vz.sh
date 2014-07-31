#!/bin/bash
# usage: ./copy-vz.sh <old-id> <new-node> <new-id>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ]; then
    exit 1
fi

oldid=$1
newnode=$2
newid=$3
VZ_CONF_ROOT=/etc/vz/conf
VZ_PRIVATE_ROOT=/home/vz/private
VZ_MOUNT_ROOT=/home/vz/root
HOSTNAME=scgyshell-$newnode

if [ ! -f "$VZ_CONF_ROOT/$oldid.conf" ]; then
    echo "VZ config does not exist"
    exit 1
fi

SSH_PARAMS="-o StrictHostKeyChecking=no -o PasswordAuthentication=no"
if ! ssh $SSH_PARAMS $HOSTNAME ls $VZ_CONF_ROOT >/dev/null; then
    echo "$VZ_CONF_ROOT does not exist on remote system $HOSTNAME" && exit 1
fi
if ssh $SSH_PARAMS $HOSTNAME cat $VZ_CONF_ROOT/$newid.conf >/dev/null; then
    echo "WARNING: $VZ_CONF_ROOT/$newid.conf already exists on remote system $HOSTNAME"
fi

scp $SSH_PARAMS $VZ_CONF_ROOT/$oldid.conf $HOSTNAME:$VZ_CONF_ROOT/$newid.conf
ssh $SSH_PARAMS $HOSTNAME mkdir -p $VZ_PRIVATE_ROOT/$newid $VZ_MOUNT_ROOT/$newid

function do_sync(){
    rsync -a -e "ssh $SSH_PARAMS" $VZ_PRIVATE_ROOT/$oldid/ $HOSTNAME:$VZ_PRIVATE_ROOT/$newid/
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

ssh $SSH_PARAMS $HOSTNAME $(dirname $0)/recover-backup.sh $newid
