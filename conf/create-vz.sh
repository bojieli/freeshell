#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password> <diskspace_softlimit> <diskspace_hardlimit>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ] || [ -z $5 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
diskspace_softlimit=$4
diskspace_hardlimit=$5
localip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
ipv6="2001:da8:d800:71::$(echo $id/10000 | bc):$(echo $id%10000 | bc)"

vzctl create $id --ostemplate debian-7.0-amd64-minimal
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
vzctl set $id --ipadd $localip --save
vzctl set $id --ipadd $ipv6 --save
vzctl set $id --nameserver 202.141.160.99 --nameserver 202.141.176.99 --save
vzctl set $id --searchdomain 6.freeshell.ustc.edu.cn --searchdomain ustc.edu.cn --save

# if home directory was backuped, recover it
if [ -d "/home/vz/backup/$id/home" ]; then
    echo "Recovering backuped /home..."
    rm -rf /home/vz/private/$id/home
    mv /home/vz/backup/$id/home /home/vz/private/$id/
fi
