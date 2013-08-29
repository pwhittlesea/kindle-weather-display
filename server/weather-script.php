<?php
// Simple Weather retrieval and display system tailored for Kindle screen
//		IN: HTTP request, Request IP (for geolocation), SVG template
//		OUT: 800x600 PNG image (and SVG file)
// 		DEPENDS: /usr/bin/convert, /usr/bin/wget
//
//		Based on: https://github.com/mpetroff/kindle-weather-display
//
//		Date: 2013-03-06
//		MorganHK
//		pwhittlesea
//


$API_key="";					// Free worldweatheronline.com API key
$cachePeriod="3600"; 			// Cache the Weather data for 3600seconds (1h)
$retrievePeriod="5"; 			// Retrieve the data for 5days
$tempFormat="C"; 				// "C" for Celsius or "F" for Fahrenheit
$queryLocation=$_SERVER['REMOTE_ADDR'];				// The location to query

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

// Equivalence table between original icons
// 		from https://github.com/mpetroff/kindle-weather-display
//		to worldweatheronline.com weather codes
$iconEquivalence["113"] = "skc";
$iconEquivalence["116"] = "sct";
$iconEquivalence["119"] = "bkn";
$iconEquivalence["122"] = "ovc";
$iconEquivalence["143"] = "hi_shwrs";
$iconEquivalence["176"] = "shra";
$iconEquivalence["179"] = "ip";
$iconEquivalence["182"] = "ip";
$iconEquivalence["185"] = "fzra";
$iconEquivalence["200"] = "tsra";
$iconEquivalence["227"] = "sn";
$iconEquivalence["230"] = "blizzard";
$iconEquivalence["248"] = "fg";
$iconEquivalence["260"] = "fg";
$iconEquivalence["263"] = "shra";
$iconEquivalence["266"] = "hi_shwrs";
$iconEquivalence["281"] = "mix";
$iconEquivalence["284"] = "mix";
$iconEquivalence["293"] = "hi_shwrs";
$iconEquivalence["296"] = "hi_shwrs";
$iconEquivalence["299"] = "ra";
$iconEquivalence["302"] = "ra";
$iconEquivalence["305"] = "ra";
$iconEquivalence["308"] = "ra";
$iconEquivalence["311"] = "rasn";
$iconEquivalence["314"] = "ip";
$iconEquivalence["317"] = "ip";
$iconEquivalence["320"] = "sn";
$iconEquivalence["323"] = "sn";
$iconEquivalence["326"] = "sn";
$iconEquivalence["329"] = "blizzard";
$iconEquivalence["332"] = "blizzard";
$iconEquivalence["335"] = "blizzard";
$iconEquivalence["338"] = "sn";
$iconEquivalence["350"] = "ip";
$iconEquivalence["353"] = "shra";
$iconEquivalence["356"] = "ra";
$iconEquivalence["359"] = "ra";
$iconEquivalence["362"] = "ip";
$iconEquivalence["365"] = "ip";
$iconEquivalence["368"] = "sn";
$iconEquivalence["371"] = "blizzard";
$iconEquivalence["374"] = "ip";
$iconEquivalence["377"] = "ip";
$iconEquivalence["386"] = "scttsra";
$iconEquivalence["389"] = "tsra";
$iconEquivalence["392"] = "tsra";
$iconEquivalence["395"] = "blizzard";

//Check for last modifiy date & update XML & PNG if needed
if (!is_file($cachedWeatherUrl) || (time()>(filemtime($cachedWeatherUrl)+$cachePeriod))){

	//get the newer version of the XML
	if(is_file($cachedWeatherUrl)) unlink($cachedWeatherUrl); // Remove old data file
	exec("/usr/bin/wget --output-document=$cachedWeatherUrl \"".$APIurl."\"");

	//Read in Weather Data
	$xml = simplexml_load_file($cachedWeatherUrl, null, LIBXML_NOCDATA);

	//Read in SVG file
	$str=file_get_contents($svgTemplate);

	//Modify SVG by replacing strings
	$str=str_replace("WEATHERDATA_CREDIT", "Data provider: World Weather Online",$str); //required by weather data-provider
	$str=str_replace("LAST_UPDATE", $xml->current_condition->localObsDateTime,$str);
	$str=str_replace("LOCATION_ONE", $xml->nearest_area->areaName,$str);
	$str=str_replace("TEMP_SYMB", $tempFormat,$str);
	$str=str_replace("REH_ONE", $xml->current_condition->humidity,$str);
	$str=str_replace("ICON_ONE", $iconEquivalence["".$xml->current_condition->weatherCode],$str);
	$str=str_replace("ICON_TWO", $iconEquivalence["".$xml->weather[1]->weatherCode],$str);
	$str=str_replace("ICON_THREE", $iconEquivalence["".$xml->weather[2]->weatherCode],$str);
	$str=str_replace("ICON_FOUR", $iconEquivalence["".$xml->weather[3]->weatherCode],$str);
	$str=str_replace("DAY_THREE", date('l', strtotime('+2 days')),$str);
	$str=str_replace("DAY_FOUR", date('l', strtotime('+3 days')),$str);
	if($tempFormat == "F"){
		$str=str_replace("TEMP_ONE", $xml->current_condition->temp_F,$str);
		$str=str_replace("HIGH_TWO", $xml->weather[1]->tempMaxF,$str);
		$str=str_replace("LOW_TWO", $xml->weather[1]->tempMinF,$str);
		$str=str_replace("HIGH_THREE", $xml->weather[2]->tempMaxF,$str);
		$str=str_replace("LOW_THREE", $xml->weather[2]->tempMinF,$str);
		$str=str_replace("HIGH_FOUR", $xml->weather[3]->tempMaxF,$str);
		$str=str_replace("LOW_FOUR", $xml->weather[3]->tempMinF,$str);
	}else{
		$str=str_replace("TEMP_ONE", $xml->current_condition->temp_C,$str);
		$str=str_replace("HIGH_TWO", $xml->weather[1]->tempMaxC,$str);
		$str=str_replace("LOW_TWO", $xml->weather[1]->tempMinC,$str);
		$str=str_replace("HIGH_THREE", $xml->weather[2]->tempMaxC,$str);
		$str=str_replace("LOW_THREE", $xml->weather[2]->tempMinC,$str);
		$str=str_replace("HIGH_FOUR", $xml->weather[3]->tempMaxC,$str);
		$str=str_replace("LOW_FOUR", $xml->weather[3]->tempMinC,$str);
	}

	//Writing out modified SVG
	if(is_file($svgProcessed))unlink($svgProcessed);
	file_put_contents($svgProcessed, $str);

	//Converting to PNG
	$cmd = "$svgProcessed $pngProcessed";
	exec("/usr/bin/convert $cmd ");

}

//Output the image from pre-processed PNG
header('Content-Type: image/png');
$fp = fopen($pngProcessed, 'rb');
  fpassthru($fp);

?>
