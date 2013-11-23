#!/bin/bash
# usage: ./entervz.sh <shellid>

if [ -z "$1" ]; then
    echo "usage: $0 <shellid>"
    exit 1
fi
id=$1
host=$(echo $id % 7 | bc)
if [ $host -eq 0 ]; then
    host=7
fi
ssh s$host.freeshell.ustc.edu.cn -t -t "sudo vzctl enter $id"
