#!/bin/sh -e

cd `dirname $0`
CONFDIR="../conf"
TMP_DIR=/tmp/freeshell-scripts
TARGET_DIR=/home/freeshell/scripts

echo "Preparing for upload..."
parallel-ssh -h pssh-hosts -P "mkdir -p $TMP_DIR"
echo "Uploading files to tmpdir..."
while read host; do
    rsync -a --delete $CONFDIR/ $host:$TMP_DIR/
done <pssh-hosts
echo "Syncing files to config dir..."
parallel-ssh -h pssh-hosts -P "sudo rsync -a --delete $TMP_DIR/ $TARGET_DIR/"

echo "Updating configs..."
parallel-ssh -h pssh-hosts -P "sudo /home/freeshell/scripts/update-conf.sh"
