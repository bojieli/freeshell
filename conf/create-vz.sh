#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password> <mem_limit> <diskspace_softlimit> <diskspace_hardlimit> <distribution> <storage_base>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ] || [ -z $5 ] || [ -z $6 ] || [ -z $7 ] || [ -z $8 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
mem_limit=$4
diskspace_softlimit=$5
diskspace_hardlimit=$6
distribution=$7
storage_base=$8

[ -f "/etc/vz/conf/$id.conf" ] && echo "removing existing vz config" && mv /etc/vz/conf/$id.conf /etc/vz/conf/$id.conf.old
STORAGE="$storage_base/private/$id"
[ -e "$STORAGE" ] && echo "removing existing vz storage" && rm -rf $STORAGE

vzctl create $id --ostemplate $distribution --private $STORAGE
vzctl set $id --userpasswd root:$password
vzctl set $id --kmemsize unlimited --save
vzctl set $id --privvmpages unlimited --save
vzctl set $id --shmpages unlimited --save
vzctl set $id --physpages $mem_limit --swappages 0 --save
vzctl set $id --diskspace $diskspace_softlimit:$diskspace_hardlimit --save
vzctl set $id --diskinodes 10000000 --save
vzctl set $id --quotatime 86400 --save
vzctl set $id --numproc 200 --save
vzctl set $id --numtcpsock 500 --save
vzctl set $id --numothersock 1000 --save
vzctl set $id --numfile 25000 --save
vzctl set $id --onboot yes --save
vzctl set $id --hostname $hostname --save
vzctl set $id --nameserver 202.141.160.99 --nameserver 202.141.176.99 --save
vzctl set $id --searchdomain 6.freeshell.ustc.edu.cn --searchdomain ustc.edu.cn --save
vzctl set $id --features ppp:on --save
vzctl set $id --devices c:108:0:rw --save
vzctl set $id --features sit:on --save
vzctl set $id --features ipip:on --save
vzctl set $id --features ipgre:on --save
vzctl set $id --features nfs:on --save
vzctl set $id --features nfsd:on --save
vzctl set $id --netfilter full --save

$(dirname $0)/recover-backup.sh $id
exit 0
