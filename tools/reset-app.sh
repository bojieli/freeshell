#!/bin/bash
if [ -z $1 ]; then
    echo "Usage: $0 <appid>"
    exit 1
fi

host=$(./getipbyappid $1)
if [ -z $host ]; then
    echo "Unknown appid $1"
    exit 1
fi
appid=$1

hostname=$(ssh $host "sudo vzlist -H -o hostname $appid")
if [ -z $hostname]; then
    echo "Unknown hostname"
    exit 1
fi

ssh $host "sudo vzctl stop $id"
ssh $host "sudo vzctl destroy $id"
passwd=$(openssl rand -base64 9)
ssh $host "sudo /home/boj/scripts/create-vz.sh $appid $hostname $passwd"
ssh $host "sudo /home/boj/scripts/activate-vz.sh $appid $host"
