#!/bin/bash

cd `dirname $0`
CONFDIR="../conf"
BASE="/home/boj/scripts"

while read host; do
    rsync -a --delete $CONFDIR/ $host:/home/boj/scripts/    
done <pssh-hosts

function prun() {
    parallel-ssh -h pssh-hosts "$1"
}

# preserve perms and execution bit, chown as root
# must ensure perm of sudoers is OK
prun "chmod 440 $BASE/etc/sudoers; \
    sudo rsync -rpE $BASE/etc/ /etc/"

KEY_SRC="$BASE/scgyshell-client.authorized_keys"
KEY_TARGET="/home/scgyshell-client/.ssh/authorized_keys"
prun "sudo chown scgyshell-client:scgyshell-client $KEY_SRC; \
    sudo chmod 600 $KEY_SRC; \
    sudo mv $KEY_SRC $KEY_TARGET"
