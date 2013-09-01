<?php
// Simple Weather retrieval and display system tailored for Kindle screen
//		IN: HTTP request, Request IP (for geolocation), SVG template
//		OUT: 800x600 PNG image (and SVG file)
//		DEPENDS: /usr/bin/convert, /usr/bin/wget
//
//		Based on: https://github.com/mpetroff/kindle-weather-display
//
//		Date: 2013-03-06
//		Original Concept - MorganHK
//		pwhittlesea
//
require("settings.php");

// Import the modules needed
require("modules/News.class.php");
require("modules/Quotes.class.php");
require("modules/Util.class.php");
require("modules/YahooWeather.class.php");

/**------------------- STATICS ------------------**/

// XML file that stores the Cal data
$calCachedData = "cal-data.xml";

// SVG template with variable names as place-holders
$svgTemplate = "weather-script-preprocess.svg";

// Output file after processing SVG template
$svgProcessed = "weather-script-output.svg";

// Output PNG file after conversion
$pngProcessed = "weather-script-output.png";

// Google schema (this means we are locked to google
$googleSchema = "http://schemas.google.com/g/2005";

/**--------------- END OF STATICS ---------------**/

// Are we debugging
Util::setDebug((isset($_GET['develop'])));

//Check for last modifiy date & update XML & PNG if needed
if (Util::$DEBUG || !is_file(YahooWeather::$FILE) || (time() > (filemtime(YahooWeather::$FILE) + $cachePeriod))){

	// Fetch our weather data
	$yahoo = new YahooWeather($queryLocation, $tempFormat);

	//Read in SVG file
	$str = file_get_contents($svgTemplate);

	//Modify SVG by replacing strings
	$values = array(
		"WEATHERDATA_CREDIT" => "Data provider: Yahoo! Weather", //required by weather data-provider
		"LAST_UPDATE" => $yahoo->getBuildDate(),
		"DAY_THREE" => date('l', strtotime('+2 days')),
		"DAY_FOUR" => date('l', strtotime('+3 days')),
		"TEMP_ONE" => $yahoo->getCurrentTemp(),
		"ICON_ONE" => $yahoo->getCurrentSymbol(),
		"ICON_TWO" => $yahoo->getSymbolForDay(1),
		"ICON_THREE" => $yahoo->getSymbolForDay(2),
		"ICON_FOUR" => $yahoo->getSymbolForDay(3),
		"HIGH_ONE" => $yahoo->getHighForDay(0),
		"HIGH_TWO" => $yahoo->getHighForDay(1),
		"HIGH_THREE" => $yahoo->getHighForDay(2),
		"HIGH_FOUR" => $yahoo->getHighForDay(3),
		"LOW_ONE" => $yahoo->getLowForDay(0),
		"LOW_TWO" => $yahoo->getLowForDay(1),
		"LOW_THREE" => $yahoo->getLowForDay(2),
		"LOW_FOUR" => $yahoo->getLowForDay(3)
	);
	$str = str_replace(array_keys($values), array_values($values), $str);

	// Are we displaying a special message
	$specialMSG = false;

	// Check the bin cal
	if (isset($userCalRSSFeed) && $userCalRSSFeed != "") {
		$cal = Util::cacheAndParseXML($calCachedData, $userCalRSSFeed);

		$now = strtotime("now");
		$binWarning = false;

		foreach ($cal->entry as $entry) {
			$when = $entry->children($googleSchema)->when;
			$timestamp = strtotime($when->attributes()->startTime . "");
			$warningStart = strtotime("-12 hours", $timestamp);
			$warningEnd = strtotime("+12 hours", $timestamp);
			if ($warningStart <= $now && $now < $warningEnd) {
				$binWarning = true;
				$str = str_replace("BIN_DAY_TYPE", strtoupper($entry->title), $str);
			}
		}
		if ($binWarning) {
			$str = str_replace("SPECIAL_ITEM", "bin_day", $str);
			$specialMSG = true;
		}
	}

	// Fetch the latest quote if its the right time
	if (Quotes::isQuoteTime()) {
		$replacements = Quotes::getLatestQuoteAndAuthor();
		$str = str_replace(array_keys($replacements), array_values($replacements), $str);
		$specialMSG = true;
	}

	// If we have a special MSG then we have less news rows
	$newsRows = ($specialMSG) ? 5 : 9;

	// Fetch the latest news stories
	$replacements = News::getLatestStories($newsRows, $userRSSFeed, $lineWrap);
	$str = str_replace(array_keys($replacements), array_values($replacements), $str);

	//Writing out modified SVG
	if (is_file($svgProcessed)) {
		unlink($svgProcessed);
	}
	file_put_contents($svgProcessed, $str);

	//Converting to PNG
	exec("/usr/bin/convert $svgProcessed $pngProcessed ");
}

if (!is_file($svgProcessed)) {
	Util::exitFor503("No image file was found to output");
}

//Output the image from pre-processed PNG
header('Content-Type: image/png');
$fp = fopen($pngProcessed, 'rb');
fpassthru($fp);
fclose($fp);
