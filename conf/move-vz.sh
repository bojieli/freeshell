#!/bin/bash
# usage: ./move-vz.sh <old-id> <new-id>

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi

oldid=$1
newid=$2
VZ_CONF_ROOT=/etc/vz/conf
VZ_PRIVATE_ROOT=/home/vz/private
VZ_MOUNT_ROOT=/home/vz/root
VZ_BACKUP_ROOT=/home/vz/backup

if [ ! -f "$VZ_CONF_ROOT/$oldid.conf" ]; then
    echo "VZ config does not exist"
    exit 1
fi

timeout 10 vzctl stop $oldid --fast

mv $VZ_CONF_ROOT/$oldid.conf $VZ_CONF_ROOT/$newid.conf
mv $VZ_PRIVATE_ROOT/$oldid $VZ_PRIVATE_ROOT/$newid
mv $VZ_BACKUP_ROOT/$oldid $VZ_BACKUP_ROOT/$newid
mkdir -p $VZ_MOUNT_ROOT/$newid
