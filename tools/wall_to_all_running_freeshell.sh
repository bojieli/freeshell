#!/bin/bash
for id in $(ls /home/vz/root/); do
	sudo timeout 3 vzctl exec $id "echo 'Sorry , this freeshell will be closed in 5 minutes to maintain , please save your work and logout as soon as possible. If you have any questions please mail to support@freeshell.ustc.edu.cn' | wall"
	echo $id 
done
