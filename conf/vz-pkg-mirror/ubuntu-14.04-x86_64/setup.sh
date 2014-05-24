#!/bin/bash
cat sources.list | vzctl exec $id "cat - > /etc/apt/sources.list"
vzctl exec $id "timeout 60 apt-get update"
