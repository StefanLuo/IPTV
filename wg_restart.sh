#!/bin/bash
#检测WireGuard网络
ping -c 4 192.168.3.1 > /dev/null 2>&1
if [ $? -ne 0 ];then
	time=$(date "+%Y-%m-%d %H:%M:%S")
	if [ -f "/tmp/wireguard_watchdog.log" ];then
		rm -f /tmp/wireguard_watchdog.log
	fi
	echo ${time}：WireGuard网络不通，准备尝试重启wg接口 >>/tmp/wireguard_watchdog.log
	ifdown wg && ifup wg
	echo ${time}：wg接口重启完成 >>/tmp/wireguard_watchdog.log
fi
