#!/bin/bash
# should be run in freeshell node!
# update configs from this dir to system dir

BASE="/home/boj/scripts"

if [ `whoami` != "root" ]; then
    echo "You are not root!"
    exit 1
fi

# preserve perms and execution bit, chown as root
rsync -rpE --exclude=sudoers $BASE/etc/ /etc/

KEY_SRC="$BASE/scgyshell-client.authorized_keys"
KEY_TARGET="/home/scgyshell-client/.ssh/authorized_keys"
chown scgyshell-client:scgyshell-client $KEY_SRC
chmod 600 $KEY_SRC
mv $KEY_SRC $KEY_TARGET

# must ensure perm and owner of sudoers is OK
chmod 440 $BASE/node-sudoers
chown root:root $BASE/node-sudoers
visudo -c -f $BASE/node-sudoers
if [ "$?" -eq 0 ]; then
    mv $BASE/node-sudoers /etc/sudoers
    exit 0
else
    exit 1
fi
