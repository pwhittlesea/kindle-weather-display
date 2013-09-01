#!/bin/sh

cd "$(dirname "$0")"

rm -f weather-script-output.png
eips -c
eips -c

if wget -q -O weather-script-output.png http://apple.pi.thega.me.uk/kindle-weather-display/server/; then
	eips -g weather-script-output.png
else
	eips -g weather-image-error.png
fi
