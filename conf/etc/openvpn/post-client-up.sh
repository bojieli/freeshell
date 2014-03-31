#!/bin/bash
# Remove redundant IPv6 routes by OpenVPN
(
    sleep 2
    ip -6 route del 2000::/3 dev tun0
    ip -6 route del 2001:da8:d800:f001::/64 dev tun0
) &
exit 0
