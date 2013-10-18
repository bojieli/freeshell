#!/bin/bash

cd `dirname $0`
CONFDIR="../conf"

echo "Uploading files..."
while read host; do
    rsync -a --delete $CONFDIR/ $host:/home/boj/scripts/    
done <pssh-hosts

echo "Updating configs..."
parallel-ssh -h pssh-hosts -P "sudo /home/boj/scripts/update-conf.sh"
