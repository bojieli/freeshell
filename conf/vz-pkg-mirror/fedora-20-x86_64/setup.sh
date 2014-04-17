#!/bin/bash
cat fedora.repo | vzctl exec $id "cat - > /etc/yum.repos.d/fedora.repo"
cat fedora-updates.repo | vzctl exec $id "cat - > /etc/yum.repos.d/fedora-updates.repo"
vzctl exec $id "yum makecache"
vzctl exec $id "yum localinstall -y --nogpgcheck http://mirrors.ustc.edu.cn/fedora/rpmfusion/free/fedora/rpmfusion-free-release-stable.noarch.rpm http://mirrors.ustc.edu.cn/fedora/rpmfusion/nonfree/fedora/rpmfusion-nonfree-release-stable.noarch.rpm"
