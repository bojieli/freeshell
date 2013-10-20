#!/bin/bash
# usage: ./setvz.sh <option> <value>

if [ -z $1 ] || [ -z $2 ] || [ -z $3 ]; then
    exit 1
fi
id=$1
option=$2
value=$3

sudo vzctl set $id --$option $value --save
