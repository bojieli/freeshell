#!/bin/bash

cd `dirname $0`
CONFDIR="../conf"

parallel-scp -A -h pssh-hosts $CONFDIR/* /home/boj/scripts/
