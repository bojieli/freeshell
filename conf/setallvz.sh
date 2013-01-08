#!/bin/bash
# usage: ./setallvz.sh <option> <value>

if [ -z $1 ] || [ -z $2 ]; then
    echo "usage: ./setallvz.sh <option> <value>";
    exit 1
fi
option=$1
value=$2

max=$(curl http://blog.ustc.edu.cn/freeshell/shellmax.php)
for id in {101..$max}; do
    host=$(echo $id % 7 | bc)
    if [ $host -eq 0 ]; then
        host=7
    fi
    ssh scgyshell-$host "sudo vzctl set $id --$option $value --save"
done
