#!/bin/bash
# this program requires iostat and ifstat.

SEPARATOR="-----FREESHELL-----";

echo "Live shell count"
echo $SEPARATOR
vzctl
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
echo "Network flow"
echo $SEPARATOR
ifstat eth0 | awk 'NR>1'
echo $SEPARATOR
