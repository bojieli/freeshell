#!/bin/bash
# should be run in freeshell node!
# update configs from this dir to system dir

BASE="/home/freeshell/scripts"

if [ -z "$1" ]; then
    NODENO=$(hostname | awk 'BEGIN{FS="-"}{print $2}')
else
    NODENO=$1
fi

if [ `whoami` != "root" ]; then
    echo "You are not root!"
    exit 1
fi

# preserve perms and execution bit, chown as root
rsync -rpE --exclude=sudoers $BASE/etc/ /etc/

# fix sysctl startup ordering
update-rc.d procps enable S >/dev/null
# load sysctl params
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

# install and patch python psutil
PSUTIL_DIR=/usr/local/lib/python2.7/dist-packages/psutil
if [ ! -d "$PSUTIL_DIR" ]; then
    apt-get update
    apt-get install -y python2.7 python2.7-dev python-pip
    pip install psutil
fi
mv $BASE/psutil-patch/_pslinux.py $PSUTIL_DIR/
mv $BASE/psutil-patch/__init__.py $PSUTIL_DIR/
