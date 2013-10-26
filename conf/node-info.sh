#!/bin/bash
# this program requires iostat.

id=$1
LS="-----FREESHELL-LINE-----";
FS="-----FREESHELL-FIELD-----";

echo $LS
echo "mystatus"
echo $FS
sudo vzctl status $id

echo $LS
echo "Live shell count"
echo $FS
vzlist | grep running | wc -l

echo $LS
echo "Load average"
echo $FS
cat /proc/loadavg

echo $LS
echo "CPU load"
echo $FS
iostat | awk 'NR>=3&&NR<=4'

echo $LS
echo "Disk usage"
echo $FS
df -lh | grep sda3

echo $LS
echo "Disk flow"
echo $FS
iostat | awk 'NR>=6&&NR<=7'

echo $LS
echo "Memory usage"
echo $FS
free -m | awk 'NR<=2'

info=$(sudo vzlist -n $1 -H -o numproc,numproc.l,numtcpsock,numtcpsock.l,numothersock,numothersock.l)

echo $LS
echo "Processes (include kernel threads)"
echo $FS
echo $info | awk '{print $1}'

echo $LS
echo "TCP sockets"
echo $FS
echo $info | awk '{print $3}'

echo $LS
echo "Non-TCP sockets"
echo $FS
echo $info | awk '{print $5}'

echo $LS
echo "#Processes (include kernel threads)"
echo $FS
echo $info | awk '{print $2}'

echo $LS
echo "#TCP sockets"
echo $FS
echo $info | awk '{print $4}'

echo $LS
echo "#Non-TCP sockets"
echo $FS
echo $info | awk '{print $6}'

echo $LS
