#!/bin/bash
# usage: ./entervz.sh <action> <shellid> [<params>]

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "usage: $0 <action> <shellid> [<params>]"
    exit 1
fi
action=$1
id=$2
params=$3
host=$(echo $id % 7 | bc)
if [ $host -eq 0 ]; then
    host=7
fi
ssh s$host.freeshell.ustc.edu.cn -t -t "sudo vzctl $action $id $params"
