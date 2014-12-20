#!/bin/sh -e

cd `dirname $0`
CONFDIR="../conf"
TARGET_DIR=/home/freeshell/scripts

echo "Uploading files..."
while read host; do
    echo "Uploading to $host"
    rsync -a -e "ssh" --rsync-path="sudo rsync" $CONFDIR/ $host:$TARGET_DIR/
done <pssh-hosts

echo "Updating configs..."
parallel-ssh -h pssh-hosts -P "sudo /home/freeshell/scripts/update-conf.sh"
