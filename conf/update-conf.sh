#!/bin/bash
# should be run in freeshell node!
# update configs from this dir to system dir

BASE="/home/boj/scripts"

if [ -z "$1" ]; then
    NODENO=$(hostname | awk 'BEGIN{FS="-"}{print $2}')
else
    NODENO=$1
fi

if [ `whoami` != "root" ]; then
    echo "You are not root!"
    exit 1
fi

export PATH=/usr/local/sbin:/usr/sbin:/sbin:/usr/local/bin:/usr/bin:/bin

# preserve perms and execution bit, chown as root
rsync -rpE --exclude=sudoers $BASE/etc/ /etc/

sysctl -p >/dev/null

KEY_SRC="$BASE/scgyshell-client.authorized_keys"
KEY_TARGET="/home/scgyshell-client/.ssh/authorized_keys"
chown scgyshell-client:scgyshell-client $KEY_SRC
chmod 600 $KEY_SRC
mv $KEY_SRC $KEY_TARGET

# must ensure perm and owner of sudoers is OK
chmod 440 $BASE/node-sudoers
chown root:root $BASE/node-sudoers
output=$(visudo -c -f $BASE/node-sudoers)
if [ "$?" -eq 0 ]; then
    mv $BASE/node-sudoers /etc/sudoers
else
    echo $output
    exit 1
fi

KEYS=/root/.ssh/authorized_keys
mv $BASE/scgyshell-root.authorized_keys $KEYS
chmod 600 $KEYS
chown root:root $KEYS

# update /etc/network/interfaces
. /etc/network/interfaces.template
