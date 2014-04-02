#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password> <diskspace_softlimit> <diskspace_hardlimit> <distribution>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ] || [ -z $5 ] || [ -z $6 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
diskspace_softlimit=$4
diskspace_hardlimit=$5
distribution=$6

vzctl create $id --ostemplate $distribution
vzctl set $id --userpasswd root:$password
vzctl set $id --kmemsize unlimited --save
vzctl set $id --privvmpages unlimited --save
vzctl set $id --shmpages unlimited --save
vzctl set $id --diskspace $diskspace_softlimit:$diskspace_hardlimit --save
vzctl set $id --diskinodes unlimited --save
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
if [ -d "/home/vz/backup/$id/home" ]; then
    echo "Recovering backuped /home..."
    rm -rf /home/vz/private/$id/home
    mv /home/vz/backup/$id/home /home/vz/private/$id/
fi
