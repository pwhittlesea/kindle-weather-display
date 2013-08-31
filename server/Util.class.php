<?php

class Util {

	public static $DEBUG;

	public static function setDebug($inDebug) {
		Util::$DEBUG = $inDebug;
	}

	public static function exitFor503($msg) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 300');
		if (Util::$DEBUG) {
			echo $msg;
		}
		exit(1);
	}

	public static function cacheAndParseXML($fileToCache, $URL) {
		//get the newer version of the XML
		if (is_file($fileToCache)) {
			// Remove old data file
			unlink($fileToCache);
		}

		exec("/usr/bin/wget -q -O $fileToCache \"".$URL."\"", $output, $return_val);
		unset($output);

		// Check that our download of the data went well
		if ($return_val != 0) {
			Util::exitFor503("Data download failed from " . $URL);
		}

		//Read in cached Weather Data
		return simplexml_load_file($fileToCache, null, LIBXML_NOCDATA);
	}
}