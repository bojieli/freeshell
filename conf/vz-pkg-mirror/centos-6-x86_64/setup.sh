#!/bin/bash
cat CentOS-Base.repo | vzctl exec $id "cat - > /etc/yum.repos.d/CentOS-Base.repo"
vzctl exec $id "yum makecache"
