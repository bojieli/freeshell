#!/bin/bash
cat sources.list | vzctl exec $id "cat - > /etc/apt/sources.list"
vzctl exec $id "apt-get update"
