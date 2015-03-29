#!/bin/bash
# usage: ./setallvz.sh <option> <value>
# if need appid in value, use $id

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "usage: ./setallvz.sh <option> <value>";
    exit 1
fi
option=$1
value=$2

max=$(curl https://freeshell.ustc.edu.cn/shellmax.php)
for id in $(seq 101 $max); do
    host=$(echo $id % 7 | bc)
    if [ $host -eq 0 ]; then
        host=7
    fi
    echo $host:$id
    realvalue=$(echo $value | sed "s/\$id/$id/g")
    ssh s$host.freeshell.ustc.edu.cn "sudo vzctl set $id --$option $realvalue --save"
done
