#!/bin/bash
# usage: ./runinallvz.sh <command>
# if need appid in command, use $id

if [ -z "$1" ]; then
    echo "usage: ./runinallvz.sh <command>";
    exit 1
fi
command="$@"

max=$(curl http://freeshell.ustc.edu.cn/shellmax.php)
for id in $(seq 101 $max); do
    host=$(echo $id % 7 | bc)
    if [ $host -eq 0 ]; then
        host=7
    fi
    echo $host:$id
    realcommand=$(echo "$command" | sed "s/\$id/$id/g")
    ssh s$host.freeshell.ustc.edu.cn "sudo vzctl exec $id '$realcommand'"
done
