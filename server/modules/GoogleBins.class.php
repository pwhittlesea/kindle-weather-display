<?php

class GoogleBins {

	// Google schema
	public static $GNS = "http://schemas.google.com/g/2005";

	// XML file that stores the Cal data
	public static $FILE = "data/cal-data.xml";

	private $xmlData;

	private $binDayType = null;

	public function __construct($userCalURL) {
		if (isset($userCalURL) && !empty($userCalURL)) {
			$this->xmlData = Util::cacheAndParseXML(GoogleBins::$FILE, $userCalURL);
		} else {
			$this->xmlData = null;
		}
	}

	public function isBinDay() {
		$this->parseXMLData();
		return ($this->binDayType != null);
	}

	public function getBinDay() {
		return array(
			"SPECIAL_ITEM" => "bin_day",
			"BIN_DAY_TYPE" => $this->binDayType
		);
	}

	private function parseXMLData() {
		if ($this->xmlData == null) {
			return false;
		}

		$now = strtotime("now");

		foreach ($this->xmlData->entry as $entry) {
			$when = $entry->children(GoogleBins::$GNS)->when;
			$timestamp = strtotime($when->attributes()->startTime . "");
			$warningStart = strtotime("-12 hours", $timestamp);
			$warningEnd = strtotime("+12 hours", $timestamp);
			if ($warningStart <= $now && $now < $warningEnd) {
				$this->binDayType = strtoupper($entry->title);
				break;
			}
		}
	}
}
