#!/bin/bash
if [ -z $1 ]; then
    echo "Usage: $0 <appid>"
    exit 1
fi

appid=$1
hostip=$(./getipbyappid $appid)
node=$(./getnodebyappid $appid)
if [ -z $node ]; then
    echo "Unknown appid $appid"
    exit 1
fi
host="s${node}.freeshell.ustc.edu.cn"

hostname=$(ssh $host "sudo vzlist -H -o hostname $appid")
if [ -z $hostname ]; then
    echo "Unknown hostname"
    exit 1
fi

read -p "Are you sure to reset freeshell $appid ($hostname)? [y/n] "
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

ssh $host "sudo vzctl stop $appid --fast"
ssh $host "sudo vzctl destroy $appid"
passwd=$(openssl rand -base64 9 | tr -d '\n\r')
ssh $host "sudo /home/boj/scripts/create-vz.sh $appid $hostname $passwd"
ssh $host "sudo /home/boj/scripts/activate-vz.sh $appid $hostip"

echo
echo "===== FREESHELL-ADMIN ====="
echo "Done. New password is: $passwd"
