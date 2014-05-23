#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password> <mem_limit> <diskspace_softlimit> <diskspace_hardlimit> <distribution>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ] || [ -z $5 ] || [ -z $6 ] || [ -z $7 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
mem_limit=$4
diskspace_softlimit=$5
diskspace_hardlimit=$6
distribution=$7

vzctl create $id --ostemplate $distribution
vzctl set $id --userpasswd root:$password
vzctl set $id --kmemsize unlimited --save
vzctl set $id --privvmpages unlimited --save
vzctl set $id --shmpages unlimited --save
vzctl set $id --physpages $mem_limit --swappages 0 --save
vzctl set $id --diskspace $diskspace_softlimit:$diskspace_hardlimit --save
vzctl set $id --diskinodes 10000000 --save
vzctl set $id --quotatime 86400 --save
vzctl set $id --numproc 500 --save
vzctl set $id --numtcpsock 500 --save
vzctl set $id --numothersock 1000 --save
vzctl set $id --numfile 25000 --save
vzctl set $id --onboot yes --save
vzctl set $id --hostname $hostname --save
vzctl set $id --nameserver 202.141.160.99 --nameserver 202.141.176.99 --save
vzctl set $id --searchdomain 6.freeshell.ustc.edu.cn --searchdomain ustc.edu.cn --save
vzctl set $id --features ppp:on --save
vzctl set $id --devices c:108:0:rw --save

# if home directory was backuped, recover it
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
