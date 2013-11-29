#!/bin/bash
# This is only a demo program to find miners in freeshell.

[ `whoami` == "root" ] || exit 1

date "+BEGIN %Y-%m-%d %H:%M:%S"
ls /proc | while read d; do
	if [[ $d = *[[:digit:]]* ]] && [ -f /proc/$d/exe ]; then
		num=$(strings /proc/$d/exe | grep miner | wc -l)
		if [ "$num" -ne 0 ]; then
			realfile=$(readlink /proc/$d/exe)
			container=$(echo $realfile | awk 'BEGIN{FS="/"}{print $5}')
			hostname=$(vzlist -H -o hostname $container)
			echo "Suspicious process [$d] detected in container $container [$hostname]: $realfile"
		fi
	fi
done
date "+END %Y-%m-%d %H:%M:%S"
