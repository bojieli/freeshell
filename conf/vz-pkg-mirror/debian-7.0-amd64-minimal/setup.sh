#!/bin/bash
cat sources.list | vzctl exec $id "cat - > /etc/apt/sources.list"
vzctl exec $id "cat - >>/var/cache/debconf/config.dat" <<EOF
Name: libraries/restart-without-asking
Template: libraries/restart-without-asking
Value: true
Owners: libssl1.0.0
Flags: seen
EOF
vzctl exec $id "timeout 60 apt-get update"
