#!/bin/bash
# usage: ./setvz.sh <id> <option> <value>

if [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ]; then
    echo "usage: ./setvz.sh <id> <option> <value>";
    exit 1
fi
id=$1
option=$2
value=$3

host=$(echo $id % 7 | bc)
if [ $host -eq 0 ]; then
    host=7
fi
ssh s$host.freeshell.ustc.edu.cn "sudo vzctl set $id --$option $value --save"
