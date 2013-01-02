#!/bin/bash
# usage: ./create-vz.sh <id> <hostname> <password> <serverip>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ] || [ -z $4 ]; then
    exit 1
fi

id=$1
hostname=$2
password=$3
localip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
serverip=$4

vzctl create $id
vzctl set $id --hostname $hostname --save
vzctl set $id --ipadd $localip --save
vzctl set $id --nameserver 202.38.64.56 --nameserver 202.38.64.17 --save
iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport 10001 -j DNAT --to-destination $localip:22
vzctl set $id --privvmpages unlimited
vzctl set $id --diskspace 5G --save
vzctl set $id --userpasswd root:$password
vzctl set $id --numproc 500
vzctl set $id --numtcpsock 100
vzctl set $id --numothersock 100
vzctl set $id --onboot yes --save
vzctl start $id
