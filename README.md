USTC freeshell
--------------

Welcome to USTC freeshell!

Freeshell is a Linux Virtual Machine service for USTCers, built with a cluster of 7 nodes.

Freeshell embraces OpenVZ virtualization technology to share resource among VMs, enabling each VM to use full capacity of hardware.

USTC Freeshell is maintained by Linux User Group @ USTC (https://lug.ustc.edu.cn).

- 2013-01-04, USTC Freeshell launched.
- 2013-04-12, Each freeshell has IPv6 address.
- 2013-04-16, freeshell.ustc.edu.cn is assigned to new freeshell.
- 2013-08-19, Freeshells have full Internet access.
- 2013-09-19, Allow multiple shells per account, sub-domain HTTP proxy.

Enjoy Freeshell at https://freeshell.ustc.edu.cn


Deployment Tips
---------------

On installation of each hardware node, download the following precreated templates as ```root```:

```
cd /home/vz/template/cache
wget http://mirrors.ustc.edu.cn/openvz/template/precreated/contrib/debian-6.0-amd64-minimal.tar.gz
wget http://mirrors.ustc.edu.cn/openvz/template/precreated/contrib/debian-7.0-amd64-minimal.tar.gz
wget http://mirrors.ustc.edu.cn/openvz/template/precreated/contrib/arch-20131014-x86_64.tar.xz
```
