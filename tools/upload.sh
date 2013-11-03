#!/bin/bash
# usage: ./upload.sh <filename> <remote-path>

if [ -z $1 ] || [ -z $2 ]; then
    echo "usage: ./upload.sh <filename> <remote-path>";
    exit 1
fi
if [ ! -e "$1" ]; then
    echo "File or directory not exist"
    exit 1
fi

echo "Uploading $1 ..."
parallel-scp -r -h $(dirname $0)/pssh-hosts $1 /home/boj
echo "Moving $1 to $2 ..."
parallel-ssh -h $(dirname $0)/pssh-hosts "sudo mv /home/boj/$1 $2"
exit 0
