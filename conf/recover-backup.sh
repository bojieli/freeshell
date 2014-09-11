#!/bin/bash
VEID=$1
[ -z "$VEID" ] && exit 1

CONFFILE="/etc/vz/conf/$VEID.conf"
[ ! -f "$CONFFILE" ] && echo "VZ conf does not exist" && exit 1
source $CONFFILE
[ -z "$VE_PRIVATE" ] && VE_PRIVATE=/home/vz/private/$VEID
[ ! -d "$VE_PRIVATE" ] && echo "vz root dir $VE_PRIVATE does not exist" && exit 1
BACKUP_DIR="$VE_PRIVATE/../../backup/$VEID"
dirlist="$BACKUP_DIR/backup-dirlist"

if [ -f "$dirlist" ]; then
    echo -n "Directories to recover: "
    cat $dirlist
    IFS="," read -ra dir <$dirlist
    for d in "${dir[@]}"; do
        echo "Recovering backuped $d ..."
        rm -rf $VE_PRIVATE/$d 2>/dev/null # clear target directory
        mkdir -p $VE_PRIVATE/$(dirname $d) # make parent directory
        mv $BACKUP_DIR/$d $VE_PRIVATE/$(dirname $d)/
    done
fi
# free backup directory
rm -rf $BACKUP_DIR
