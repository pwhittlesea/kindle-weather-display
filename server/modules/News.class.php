<?php

class News {

	// News RSS feed
	public static $URL = "http://feeds.bbci.co.uk/news/rss.xml";

	// XML file that stores the RSS data
	public static $FILE = "rss-data.xml";

	public static function getLatestStories($newsRows, $userOverrideURL, $lineWrap) {
		$url = (isset($userOverrideURL) && !empty($userOverrideURL)) ? $userOverrideURL : News::$URL;
		$lines = array(
			"STORY_1" => "",
			"STORY_2" => "",
			"STORY_3" => "",
			"STORY_4" => "",
			"STORY_5" => "",
			"STORY_6" => "",
			"STORY_7" => "",
			"STORY_8" => "",
			"STORY_9" => "",
		);

		// Fetch our RSS feed data
		$rss = Util::cacheAndParseXML(News::$FILE, $url);

		$stories = array();
		for ($x = 0; $x <= $newsRows; $x++) {
			$headline = "" . $rss->channel->item[$x]->title;
			if ($lineWrap) {
				$stories = array_merge($stories, explode("<br>", wordwrap($headline, 45, "<br>")));
			} else {
				if (strlen($headline) > 45) {
					$headline = substr($headline, 0, 43);
					$headline = substr($headline, 0, strrpos($headline, " ")) . "...";
				}
				$stories[] = $headline;
			}
		}

		for ($x = 1; $x <= $newsRows; $x++) {
			$lines["STORY_" . $x] =  $stories[$x - 1];
		}

		return $lines;
	}

}