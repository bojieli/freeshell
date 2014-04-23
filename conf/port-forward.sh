#!/bin/bash
# usage: ./port-forward.sh <action> <local-port> <shell-id> <shell-port>

if [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ] || [ -z "$4" ]; then
    exit 1
fi

action=$1
local_port=$2
id=$3
shell_ip="10.10.$(echo $id/256 | bc).$(echo $id%256 | bc)"
shell_port=$4

function iptables_delete(){
    iptables -t nat -D $@
}
function iptables_replace(){
    iptables -t nat -D $@
    iptables -t nat -A $@
}

if [ "$action" == "add" ]; then
    iptables_replace PREROUTING -i eth0 -p tcp -m tcp --dport $local_port -j DNAT --to-destination $shell_ip:$shell_port
fi

if [ "$action" == "remove" ]; then
    iptables_delete PREROUTING -i eth0 -p tcp -m tcp --dport $local_port -j DNAT --to-destination $shell_ip:$shell_port
fi

iptables-save > /home/freeshell/iptables-save
