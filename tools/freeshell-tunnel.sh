#!/bin/bash

NODENUM=7
for node in `seq 1 $NODENUM`; do
    ip tunnel add freeshell$node mode ipip remote $(dig +short s$node.freeshell.ustc.edu.cn) local 202.141.160.99
    ip link set freeshell$node up
    ip addr add 10.71.$node.1/30 dev freeshell$node
done

max=$(curl http://freeshell.ustc.edu.cn/shellmax.php)
[ -z "$max" ] && echo "Cannot find shell max" && exit 1

# the following table should have been added by "ip rule"
tableid=1002
for shellid in `seq 101 $max`; do
    ip=10.10.$(($shellid / 256)).$(($shellid % 256))
    node=$(($shellid % $NODENUM))
    [ $node -eq 0 ] && node=$NODENUM
    gw=10.71.$node.2
    echo "route replace $ip/32 via $gw table $tableid"
done | ip -batch -
