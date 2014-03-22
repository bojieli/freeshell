#!/bin/bash
# usage: ./activate-vz.sh <id> <serverip> [renew]

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi

id=$1
serverip=$2
localip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
sshport=$(echo $id + 10000 | bc)
httpport=$(echo $id + 20000 | bc)
ipv6="2001:da8:d800:71::$(echo $id/10000 | bc):$(echo $id%10000 | bc)"

if [ "$3" == "renew" ]; then
    iptables -t nat -D PREROUTING -i eth0 -p tcp -d $serverip --dport $sshport -j DNAT --to-destination $localip:22
    iptables -t nat -D PREROUTING -i eth0 -p tcp -d $serverip --dport $httpport -j DNAT --to-destination $localip:80
fi
iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport $sshport -j DNAT --to-destination $localip:22
iptables -t nat -A PREROUTING -i eth0 -p tcp -d $serverip --dport $httpport -j DNAT --to-destination $localip:80
iptables-save > /home/boj/iptables-save

vzctl set $id --ipdel all --save
vzctl set $id --ipadd $localip --save
vzctl set $id --ipadd $ipv6 --save

vzctl start $id
vzctl exec $id mknod /dev/ppp c 108 0
vzctl exec $id chmod 600 /dev/ppp

cat `dirname $0`/conf-in-vz/sources.list | vzctl exec $id "cat - > /etc/apt/sources.list"
cat `dirname $0`/conf-in-vz/locale.gen | vzctl exec $id "cat - > /etc/locale.gen"
vzctl exec $id "locale-gen"
vzctl exec $id "update-locale LANG=en_US.utf8"

# fix timezone
vzctl exec $id "ln -sf /usr/share/zoneinfo/Asia/Chongqing /etc/localtime"
vzctl exec $id "echo Asia/Chongqing > /etc/timezone"

sleep 2 # for network bootstrap
vzctl exec $id "apt-get update"
