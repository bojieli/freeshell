#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
localip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
ipv6="2001:da8:d800:71::$(echo $id/10000 | bc):$(echo $id%10000 | bc)"

vzctl create $id
vzctl set $id --userpasswd root:$password
vzctl set $id --kmemsize unlimited --save
vzctl set $id --privvmpages unlimited --save
vzctl set $id --shmpages unlimited --save
vzctl set $id --diskspace 5G:7G --save
vzctl set $id --diskinodes unlimited --save
vzctl set $id --quotatime 86400 --save
vzctl set $id --numproc 200 --save
vzctl set $id --numtcpsock 100 --save
vzctl set $id --numothersock 100 --save
vzctl set $id --onboot yes --save
vzctl set $id --hostname $hostname --save
vzctl set $id --ipadd $localip --save
vzctl set $id --ipadd $ipv6 --save
vzctl set $id --nameserver 202.38.64.56 --nameserver 202.38.64.17 --save
