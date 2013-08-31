<?php
// Simple Weather retrieval and display system tailored for Kindle screen
//		IN: HTTP request, Request IP (for geolocation), SVG template
//		OUT: 800x600 PNG image (and SVG file)
//		DEPENDS: /usr/bin/convert, /usr/bin/wget
//
//		Based on: https://github.com/mpetroff/kindle-weather-display
//
//		Date: 2013-03-06
//		MorganHK
//		pwhittlesea
//
include_once("settings.php");

/**------------------- STATICS ------------------**/

// XML file that stores the weather data
$cachedWeatherUrl = "weather-data.xml";

// SVG template with variable names as place-holders
$svgTemplate = "weather-script-preprocess.svg";

// Output file after processing SVG template
$svgProcessed = "weather-script-output.svg";

// Output PNG file after conversion
$pngProcessed = "weather-script-output.png";

// The base URL for the weather service
$weatherOnlineURL = "http://api.worldweatheronline.com/free/v1/weather.ashx";

// News RSS feed
$cachedRSSFeed = "http://feeds.bbci.co.uk/news/rss.xml";

// XML file that stores the RSS data
$cachedRSSData = "rss-data.xml";

/**--------------- END OF STATICS ---------------**/

// The location to query
$queryLocation = (isset($staticLocation) && $staticLocation != "") ? $staticLocation : $_SERVER['REMOTE_ADDR'];

// Check the user hasn't overridden the RSS location
$cachedRSSFeed = (isset($userRSSFeed) && $userRSSFeed != "") ? $userRSSFeed : $cachedRSSFeed;

// Are we debugging
$debug = (isset($_GET['develop'])) ? true : false;

// API URL
$APIurl = $weatherOnlineURL.'?q='.$queryLocation.'&extra=localObsTime&includeLocation=yes&format=xml&num_of_days='.$retrievePeriod.'&key='.$API_key;

// Equivalence table between
// original icons from https://github.com/mpetroff/kindle-weather-display
// to worldweatheronline.com weather codes
$iconEquivalence = array(
	"113" => "skc",
	"116" => "sct",
	"119" => "bkn",
	"122" => "ovc",
	"143" => "hi_shwrs",
	"176" => "shra",
	"179" => "ip",
	"182" => "ip",
	"185" => "fzra",
	"200" => "tsra",
	"227" => "sn",
	"230" => "blizzard",
	"248" => "fg",
	"260" => "fg",
	"263" => "shra",
	"266" => "hi_shwrs",
	"281" => "mix",
	"284" => "mix",
	"293" => "hi_shwrs",
	"296" => "hi_shwrs",
	"299" => "ra",
	"302" => "ra",
	"305" => "ra",
	"308" => "ra",
	"311" => "rasn",
	"314" => "ip",
	"317" => "ip",
	"320" => "sn",
	"323" => "sn",
	"326" => "sn",
	"329" => "blizzard",
	"332" => "blizzard",
	"335" => "blizzard",
	"338" => "sn",
	"350" => "ip",
	"353" => "shra",
	"356" => "ra",
	"359" => "ra",
	"362" => "ip",
	"365" => "ip",
	"368" => "sn",
	"371" => "blizzard",
	"374" => "ip",
	"377" => "ip",
	"386" => "scttsra",
	"389" => "tsra",
	"392" => "tsra",
	"395" => "blizzard"
);

function exitFor503($msg) {
	global $debug;

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 300');
	if ($debug) {
		echo $msg;
	}
	exit(1);
}

//Check for last modifiy date & update XML & PNG if needed
if ($debug || !is_file($cachedWeatherUrl) || (time() > (filemtime($cachedWeatherUrl) + $cachePeriod))){

	//get the newer version of the XML
	if (is_file($cachedWeatherUrl)) {
		// Remove old data file
		unlink($cachedWeatherUrl);
	}
	exec("/usr/bin/wget -q -O $cachedWeatherUrl \"".$APIurl."\"", $output, $return_val);
	unset($output);

	// Check that our download of the data went well
	if ($return_val != 0) {
		exitFor503("Data download failed from " . $APIurl);
	}

	//Read in cached Weather Data
	$xml = simplexml_load_file($cachedWeatherUrl, null, LIBXML_NOCDATA);

	if (isset($xml->error)) {
		exitFor503($xml->error->msg);
	}

	//Read in SVG file
	$str = file_get_contents($svgTemplate);

	//Modify SVG by replacing strings
	$values = array(
		"WEATHERDATA_CREDIT" => "Data provider: World Weather Online", //required by weather data-provider
		"LAST_UPDATE" => $xml->current_condition->localObsDateTime,
		"LOCATION_ONE" => $xml->nearest_area->areaName,
		"TEMP_SYMB" => $tempFormat,
		"REH_ONE" => $xml->current_condition->humidity,
		"ICON_ONE" => $iconEquivalence["".$xml->current_condition->weatherCode],
		"ICON_TWO" => $iconEquivalence["".$xml->weather[1]->weatherCode],
		"ICON_THREE" => $iconEquivalence["".$xml->weather[2]->weatherCode],
		"ICON_FOUR" => $iconEquivalence["".$xml->weather[3]->weatherCode],
		"DAY_THREE" => date('l', strtotime('+2 days')),
		"DAY_FOUR" => date('l', strtotime('+3 days'))
	);
	$str = str_replace(array_keys($values), array_values($values), $str);

	if($tempFormat == "F"){
		$tempValues = array(
			"HIGH_ONE" => $xml->weather[0]->tempMaxF,
			"LOW_ONE" => $xml->weather[0]->tempMinF,
			"TEMP_ONE" => $xml->current_condition->temp_F,
			"HIGH_TWO" => $xml->weather[1]->tempMaxF,
			"LOW_TWO" => $xml->weather[1]->tempMinF,
			"HIGH_THREE" => $xml->weather[2]->tempMaxF,
			"LOW_THREE" => $xml->weather[2]->tempMinF,
			"HIGH_FOUR" => $xml->weather[3]->tempMaxF,
			"LOW_FOUR" => $xml->weather[3]->tempMinF
		);
	}else{
		$tempValues = array(
			"HIGH_ONE" => $xml->weather[0]->tempMaxC,
			"LOW_ONE" => $xml->weather[0]->tempMinC,
			"TEMP_ONE" => $xml->current_condition->temp_C,
			"HIGH_TWO" => $xml->weather[1]->tempMaxC,
			"LOW_TWO" => $xml->weather[1]->tempMinC,
			"HIGH_THREE" => $xml->weather[2]->tempMaxC,
			"LOW_THREE" => $xml->weather[2]->tempMinC,
			"HIGH_FOUR" => $xml->weather[3]->tempMaxC,
			"LOW_FOUR" => $xml->weather[3]->tempMinC
		);
	}
	$str = str_replace(array_keys($tempValues), array_values($tempValues), $str);

	exec("/usr/bin/wget -q -O $cachedRSSData \"".$cachedRSSFeed."\"");
	$rss = simplexml_load_file($cachedRSSFeed, null, LIBXML_NOCDATA);

	$lines = array();
	for ($x = 0; $x <= 10; $x++) {
		$headline = "" . $rss->channel->item[$x]->title;
		if ($lineWrap) {
			$lines = array_merge($lines, explode("<br>", wordwrap($headline, 45, "<br>")));
		} else {
			if (strlen($headline) > 45) {
				$headline = substr($headline, 0, 43);
				$headline = substr($headline, 0, strrpos($headline, " ")) . "...";
			}
			$lines[] = $headline;
		}
	}

	$stories = array();
	for ($x = 1; $x <= 10; $x++) {
		$stories["STORY_" . $x] =  $lines[$x-1];
	}

	$str = str_replace(array_keys($stories), array_values($stories), $str);

	//Writing out modified SVG
	if (is_file($svgProcessed)) {
		unlink($svgProcessed);
	}
	file_put_contents($svgProcessed, $str);

	//Converting to PNG
	exec("/usr/bin/convert $svgProcessed $pngProcessed ");
}

if (!is_file($svgProcessed)) {
	exitFor503("No image file was found to output");
}

//Output the image from pre-processed PNG
header('Content-Type: image/png');
$fp = fopen($pngProcessed, 'rb');
fpassthru($fp);
fclose($fp);
