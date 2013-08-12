#!/bin/bash

cd `dirname $0`
CONFDIR="../conf"
BASE="/home/boj/scripts"

while read host; do
    rsync -a --delete $CONFDIR/ $host:/home/boj/scripts/    
done <pssh-hosts

function prun() {
    parallel-ssh -h pssh-hosts "sudo $1"
}

# preserve perms and execution bit, chown as root
prun "rsync -rpE $BASE/etc/ /etc/"
prun "rsync -rpE $BASE/scgyshell-client.authorized_keys /home/scgyshell-client/.ssh/authorized_keys"
