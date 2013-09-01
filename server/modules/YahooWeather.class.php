<?php

class YahooWeather {

	public static $URL = "http://weather.yahooapis.com/forecastrss";

	public static $YNS = "http://xml.weather.yahoo.com/ns/rss/1.0";

	// XML file that stores the weather data
	public static $FILE = "data/weather-data.xml";

	private $conversion = array(
		"0" => "tornado",
		"1" => "tropical storm",
		"2" => "hurricane",
		"3" => "severe thunderstorms",
		"4" => "thunderstorms",
		"5" => "mixed rain and snow",
		"6" => "mixed rain and sleet",
		"7" => "mixed snow and sleet",
		"8" => "freezing drizzle",
		"9" => "drizzle",
		"10" => "freezing rain",
		"11" => "showers",
		"12" => "showers",
		"13" => "snow flurries",
		"14" => "light snow showers",
		"15" => "blowing snow",
		"16" => "snow",
		"17" => "hail",
		"18" => "sleet",
		"19" => "dust",
		"20" => "foggy",
		"21" => "haze",
		"22" => "smoky",
		"23" => "blustery",
		"24" => "windy",
		"25" => "cold",
		"26" => "cloudy",
		"27" => "mostly cloudy (night)",
		"28" => "mostly cloudy (day)",
		"29" => "partly cloudy (night)",
		"30" => "partly cloudy (day)",
		"31" => "clear (night)",
		"32" => "sunny",
		"33" => "fair (night)",
		"34" => "fair (day)",
		"35" => "mixed rain and hail",
		"36" => "hot",
		"37" => "isolated thunderstorms",
		"38" => "scattered thunderstorms",
		"39" => "scattered thunderstorms",
		"40" => "scattered showers",
		"41" => "heavy snow",
		"42" => "scattered snow showers",
		"43" => "heavy snow",
		"44" => "partly cloudy",
		"45" => "thundershowers",
		"46" => "snow showers",
		"47" => "isolated thundershowers",
		"3200" => "not available"
	);

	private $pngNames = array(
		"mostly cloudy (night)" => "bkn",
		"mostly cloudy (day)" => "bkn",
		"blowing snow" => "blizzard",
		"cold" => "cold",
		"dust" => "du",
		"foggy" => "fg",
		"smoky" => "fu",
		"freezing rain" => "fzra",
		"freezing drizzle" => "fzra",
		"scattered showers" => "hi_shwrs",
		"hot" => "hot",
		"sleet" => "ip",
		"hail" => "ip",
		"mixed snow and sleet" => "mix",
		"mixed rain and hail" => "mix",
		"cloudy" => "ovc",
		"showers" => "ra",
		"mixed rain and sleet" => "raip",
		"mixed rain and snow" => "rasn",
		"partly cloudy" => "sct",
		"partly cloudy (night)" => "sct",
		"partly cloudy (day)" => "sct",
		"haze" => "sctfg",
		"isolated thundershowers" => "scttsra",
		"isolated thunderstorms" => "scttsra",
		"scattered thunderstorms" => "scttsra",
		"drizzle" => "shra",
		"sunny" => "skc",
		"clear (night)" => "skc",
		"fair (night)" => "few",
		"fair (day)" => "few",
		"snow showers" => "sn",
		"heavy snow" => "sn",
		"snow" => "sn",
		"snow flurries" => "sn",
		"scattered snow showers" => "sn",
		"light snow showers" => "sn",
		"thundershowers" => "tsra",
		"thunderstorms" => "tsra",
		"severe thunderstorms" => "tsra",
		"blustery" => "wind",
		"windy" => "wind",
		"tornado" => "wind",
		"tropical storm" => "wind",
		"hurricane" => "wind",
	);

	private $xmlData;

	public function __construct($queryLocation, $tempFormat) {
		// API URL
		$APIurl = YahooWeather::$URL . '?w=' . $queryLocation . '&u=' . strtolower($tempFormat);
		$this->xmlData = Util::cacheAndParseXML(YahooWeather::$FILE, $APIurl);
		// $this->xmlData = simplexml_load_file(YahooWeather::$FILE, null, LIBXML_NOCDATA);
	}

	public function getBuildDate() {
		return $this->xmlData->channel->lastBuildDate;
	}

	public function getSymbolForDay($dayNumber) {
		$code = $this->getDay($dayNumber)->code;
		return $this->pngNames[$this->conversion[(string) $code]];
	}

	public function getCurrentSymbol() {
		$y_children = $this->xmlData->channel->item->children(YahooWeather::$YNS);
		$condition = $y_children->condition->attributes();
		$code = $condition->code;
		return $this->pngNames[$this->conversion[(string) $code]];
	}

	public function getCurrentTemp() {
		$y_children = $this->xmlData->channel->item->children(YahooWeather::$YNS);
		$condition = $y_children->condition->attributes();
		return $condition->temp;
	}

	public function getHighForDay($dayNumber) {
		return $this->getDay($dayNumber)->high;
	}

	public function getLowForDay($dayNumber) {
		return $this->getDay($dayNumber)->low;
	}

	private function getDay($dayNumber) {
		$y_children = $this->xmlData->channel->item->children(YahooWeather::$YNS);
		return $y_children->forecast[$dayNumber]->attributes();
	}
}
