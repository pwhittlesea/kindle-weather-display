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

$API_key = "";										// Free worldweatheronline.com API key
$cachePeriod = "3600";								// Cache the Weather data for 3600 seconds (1h)
$retrievePeriod = "5";								// Retrieve the data for 5days
$tempFormat = "C";									// "C" for Celsius or "F" for Fahrenheit
$queryLocation = $_SERVER['REMOTE_ADDR'];				// The location to query

$cachedWeatherUrl = "weather-data.xml";				// XML file that stores the weather data
$svgTemplate = "weather-script-preprocess.svg";		// SVG template with variable names as place-holders
$svgProcessed = "weather-script-output.svg";		// Output file after processing SVG template
$pngProcessed = "weather-script-output.png";		// Output PNG file after conversion
$weatherOnlineURL = "http://api.worldweatheronline.com/free/v1/weather.ashx"; // The base URL for the weather service

// If the user chooses to use a postcode instead of the IP
if (isset($_GET['POSTCODE'])) {
	$queryLocation = $_GET['POSTCODE'];
}

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

//Check for last modifiy date & update XML & PNG if needed
if (!is_file($cachedWeatherUrl) || (time() > (filemtime($cachedWeatherUrl) + $cachePeriod))){

	//get the newer version of the XML
	if(is_file($cachedWeatherUrl)) unlink($cachedWeatherUrl); // Remove old data file
	exec("/usr/bin/wget --output-document=$cachedWeatherUrl \"".$APIurl."\"");

	//Read in Weather Data
	$xml = simplexml_load_file($cachedWeatherUrl, null, LIBXML_NOCDATA);

	//Read in SVG file
	$str=file_get_contents($svgTemplate);

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

	//Writing out modified SVG
	if (is_file($svgProcessed)) {
		unlink($svgProcessed);
	}
	file_put_contents($svgProcessed, $str);

	//Converting to PNG
	exec("/usr/bin/convert $svgProcessed $pngProcessed ");

}

//Output the image from pre-processed PNG
header('Content-Type: image/png');
$fp = fopen($pngProcessed, 'rb');
fpassthru($fp);
fclose($fp);
