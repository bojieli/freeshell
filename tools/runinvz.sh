#!/bin/bash
# usage: ./runinvz.sh <shellid> <command>

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "usage: $0 <shellid> <command>"
    exit 1
fi
id=$1
shift
host=$(echo $id % 7 | bc)
if [ $host -eq 0 ]; then
    host=7
fi
ssh s$host.freeshell.ustc.edu.cn "sudo vzctl exec $id $@"
