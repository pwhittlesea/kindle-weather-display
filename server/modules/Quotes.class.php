<?php

class Quotes {

	public static $URL = "http://feeds.feedburner.com/quotationspage/mqotd";

	public static $FILE = "quote-data.xml";

	public static function getLatestQuoteAndAuthor() {
		$lineWidth = 55;
		$lines = array(
			"QUOTE_0" => "",
			"QUOTE_1" => "",
			"QUOTE_2" => "",
			"QUOTE_3" => "",
			"QUOTE_4" => "",
		);
		$x = 0;

		$xmlData = Util::cacheAndParseXML(Quotes::$FILE, Quotes::$URL);
		foreach ($xmlData->channel->item as $item) {
			$desc = $item->description . " ";
			if (strlen($desc) + 1 < $lineWidth * 4) {
				while (strlen($desc) > 1) {
					$subStr = substr($desc, 0, $lineWidth + 1);
					$lastSpace = strrpos($subStr, " ");
					$lines["QUOTE_" . $x++] = substr($desc, 0, $lastSpace);
					$desc = substr($desc, $lastSpace);
				}
				$lines["QUOTE_" . $x] = "-- " . $item->title;
				return array_merge(array(
					"SPECIAL_ITEM" => "qotd"
				), $lines);
			}
		}
	}

	public static function isQuoteTime() {
		$now = +date("His");
		$midday = +"120000";
		$eleven = +"230000";
		return ($now < $midday || $now > $eleven);
	}
}