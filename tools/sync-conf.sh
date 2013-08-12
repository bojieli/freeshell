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
prun "chmod 440 /etc/sudoers"

KEY_SRC="$BASE/scgyshell-client.authorized_keys"
KEY_TARGET="/home/scgyshell-client/.ssh/authorized_keys"
prun "chown scgyshell-client:scgyshell-client $KEY_SRC"
prun "chmod 600 $KEY_SRC"
prun "mv $KEY_SRC $KEY_TARGET"
