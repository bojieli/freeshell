#!/bin/bash
# usage: ./runinallvz.sh <command>

if [ -z $1 ]; then
    echo "usage: ./runinallvz.sh <command>";
    exit 1
fi
command=$@

max=$(curl http://blog.ustc.edu.cn/freeshell/shellmax.php)
for id in $(seq 101 $max); do
    host=$(echo $id % 7 | bc)
    if [ $host -eq 0 ]; then
        host=7
    fi
    echo $host:$id
    ssh scgyshell-$host "sudo vzctl exec $id $command"
done
