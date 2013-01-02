#!/bin/bash
# usage: ./upload.sh <filename> <remote-path>

if [ -z $1 ] || [ -z $2 ]; then
    echo "usage: ./upload.sh <filename> <remote-path>";
    exit 1
fi

echo "Uploading $1 ..."
psshscp -h pssh-hosts $1 /home/boj
echo "Moving $1 to $2 ..."
pssh -h pssh-hosts "sudo mv /home/boj/$1 $2"
exit 0
