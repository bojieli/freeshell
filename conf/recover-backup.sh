#!/bin/bash
id=$1
[ -z "$id" ] && exit 1

dirlist="/home/vz/backup/$id/backup-dirlist"
if [ -f "$dirlist" ]; then
    echo -n "Directories to recover: "
    cat $dirlist
    IFS="," read -ra dir <$dirlist
    for d in "${dir[@]}"; do
        echo "Recovering backuped $d ..."
        rm -rf /home/vz/private/$id/$d 2>/dev/null
        mkdir -p /home/vz/private/$id/$(dirname $d)
        mv /home/vz/backup/$id/$d /home/vz/private/$id/$(dirname $d)
    done
fi
# free backup directory
rm -rf /home/vz/backup/$id
