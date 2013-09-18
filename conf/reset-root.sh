#!/bin/bash
# usage: ./reset-root.sh <id> <new-root-password>

if [ -z $1 ] || [ -z $2 ]; then
    exit 1
fi
id=$1
newpass=$2

vzctl set $id --userpasswd root:$newpass
