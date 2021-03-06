#!/bin/bash
# usage: ./activate-vz.sh <id> <serverip> <distribution> [renew]

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ]; then
    exit 1
fi

cd `dirname $0`

id=$1
serverip=$2
distribution=$3
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
iptables-save > /home/freeshell/iptables-save

vzctl set $id --ipdel all --save
vzctl set $id --ipadd $localip --save
vzctl set $id --ipadd $ipv6 --save

vzctl start $id
vzctl exec $id mknod /dev/ppp c 108 0
vzctl exec $id chmod 600 /dev/ppp

cat conf-in-vz/locale.gen | vzctl exec $id "cat - > /etc/locale.gen"
vzctl exec $id "locale-gen"
vzctl exec $id "update-locale LANG=en_US.utf8"

# fix timezone
vzctl exec $id "ln -sf /usr/share/zoneinfo/Asia/Chongqing /etc/localtime"
vzctl exec $id "echo Asia/Chongqing > /etc/timezone"

sleep 2 # for network bootstrap

DIR="vz-pkg-mirror/$distribution"
if [ -d "$DIR" ] && [ -f "$DIR/setup.sh" ]; then
    pushd $DIR  >/dev/null 2>&1
    source setup.sh
    popd        >/dev/null 2>&1
fi
