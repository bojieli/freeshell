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
echo $localip

sudo vzctl create $id
sudo vzctl set $id --hostname $hostname --save
sudo vzctl set $id --ipadd $localip --save
sudo vzctl set $id --nameserver 202.38.64.56 --nameserver 202.38.64.17 --save
sudo iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport 10001 -j DNAT --to-destination $localip:22
sudo vzctl set $id --privvmpages unlimited
sudo vzctl set $id --diskspace 5G --save
sudo vzctl set $id --userpasswd root:$password
sudo vzctl set $id --numproc 500
sudo vzctl set $id --numtcpsock 100
sudo vzctl set $id --numothersock 100
sudo vzctl set $id --onboot yes --save
sudo vzctl start $id
