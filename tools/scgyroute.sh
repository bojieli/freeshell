#!/bin/sh

ip route del default
ip route add default 		via 192.168.0.2   dev eth0
ip route add 202.38.64.0/19 	via 202.38.70.254 dev eth0 
ip route add 210.45.64.0/20	via 202.38.70.254 dev eth0 
ip route add 210.45.112.0/20 	via 202.38.70.254 dev eth0 
ip route add 211.86.144.0/20 	via 202.38.70.254 dev eth0 
ip route add 222.195.64.0/19 	via 202.38.70.254 dev eth0 
ip route add 114.214.160.0/19 	via 202.38.70.254 dev eth0 
ip route add 114.214.192.0/18 	via 202.38.70.254 dev eth0 
ip route add 210.72.22.0/24 	via 202.38.70.254 dev eth0 
ip route add 218.22.21.0/27 	via 202.38.70.254 dev eth0 
ip route add 218.104.71.160/28 	via 202.38.70.254 dev eth0 
ip route add 202.141.160.0/19 	via 202.38.70.254 dev eth0 
ip route add 202.141.160.0/20 	via 202.38.70.254 dev eth0 
ip route add 202.141.176.0/20 	via 202.38.70.254 dev eth0 
ip route add 121.255.0.0/16 	via 202.38.70.254 dev eth0
