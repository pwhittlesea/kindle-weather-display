#!/bin/sh

## VARS
kitersrc=/mnt/base-us/kite/.rsrc
kiteconfig=/mnt/base-us/kite/config
usbipv4=`head -1 $kiteconfig/usbipv4`
sleepytime=10

## ENABLE USBNET
if lsmod|grep -q g_ether; then
	#Do nothing since we are already with USB ethernet on
	sleep 1
else
	rmmod g_file_storage
	modprobe g_ether
	ifconfig usb0 $usbipv4
	mount /mnt/base-us -o remount,exec
	$kitersrc/sbin/xinetd
fi

## KILL KINDLE FRAMEWORK
/etc/init.d/framework stop
/etc/init.d/powerd stop

## LOOP WEATHER DISPLAY
while true; do
	/mnt/us/weather/display-weather.sh
	sleep $sleepytime
done