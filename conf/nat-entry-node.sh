#!/bin/bash
# usage: ./nat-entry-node.sh <id> <localip> <nodeip> [renew]

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ]; then
    exit 1
fi

id=$1
localip=$2
nodeip=$3
sshport=$(echo $id + 10000 | bc)
httpport=$(echo $id + 20000 | bc)

if [ $localip == $nodeip ]; then
	exit 1
fi

if [ "$4" == "renew" ]; then
    iptables -t nat -D PREROUTING -i eth0 -p tcp --dport $sshport -j DNAT --to-destination $nodeip:$sshport
    iptables -t nat -D POSTROUTING -p tcp --dport $sshport -j SNAT --to-source $localip
    iptables -t nat -D PREROUTING -i eth0 -p tcp --dport $httpport -j DNAT --to-destination $nodeip:$httpport
    iptables -t nat -D POSTROUTING -p tcp --dport $httpport -j SNAT --to-source $localip
fi

iptables -t nat -A PREROUTING -i eth0 -p tcp --dport $sshport -j DNAT --to-destination $nodeip:$sshport
iptables -t nat -A POSTROUTING -p tcp --dport $sshport -j SNAT --to-source $localip
iptables -t nat -A PREROUTING -i eth0 -p tcp --dport $httpport -j DNAT --to-destination $nodeip:$httpport
iptables -t nat -A POSTROUTING -p tcp --dport $httpport -j SNAT --to-source $localip
iptables-save > /home/freeshell/iptables-save
