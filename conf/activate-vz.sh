#!/bin/bash
# usage: ./activate-vz.sh <id> <serverip>

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi

id=$1
serverip=$2
localip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
sshport=$(echo $id + 10000 | bc)
httpport=$(echo $id + 20000 | bc)

iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport $sshport -j DNAT --to-destination $localip:22
iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport $httpport -j DNAT --to-destination $localip:80
iptables-save > /home/boj/iptables-save
vzctl start $id
cat `dirname $0`/sources.list | vzctl exec $id "cat - > /etc/apt/sources.list"
sleep 2 # for network bootstrap
vzctl exec $id "apt-get update"
vzctl exec $id "apt-get -y install fail2ban"
