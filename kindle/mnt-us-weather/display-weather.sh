#!/bin/sh

cd "$(dirname "$0")"

rm weather-script-output.png
eips -c
eips -c

if wget -O weather-script-output.png http://server/path/to/weather-script.php; then
	eips -g weather-script-output.png
else
	eips -g weather-image-error.png
fi
