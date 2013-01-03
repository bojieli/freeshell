#!/bin/bash
# this program requires iostat.

id=$1
SEPARATOR="-----FREESHELL-----";

echo "mystatus"
echo $SEPARATOR
sudo vzctl status $id
echo $SEPARATOR
echo "Live shell count"
echo $SEPARATOR
vzlist | grep running | wc -l
echo $SEPARATOR
echo "Load average"
echo $SEPARATOR
cat /proc/loadavg
echo $SEPARATOR
echo "CPU load"
echo $SEPARATOR
iostat | awk 'NR>=3&&NR<=4'
echo $SEPARATOR
echo "Disk usage"
echo $SEPARATOR
df -lh | grep sda3
echo $SEPARATOR
echo "Disk flow"
echo $SEPARATOR
iostat | awk 'NR>=6&&NR<=7'
echo $SEPARATOR
echo "Memory usage"
echo $SEPARATOR
free -m | awk 'NR<=2'
echo $SEPARATOR