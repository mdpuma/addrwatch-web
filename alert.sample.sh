#!/bin/bash

echo "$@"

# echoing to remote syslog server 
echo "$@" | logger -t goarpwatch -n 185.181.228.2



curl "https://local.lan/addrwatch/push.php?action=$1&ip_address=$2&mac_address=$3&interface=$4&hostname=$(hostname -f)&vlan_tag=801"
