Origins
=======
For more information on the orignal inspiration see [the original blog post](http://www.mpetroff.net/archives/2012/09/14/kindle-weather-display/).

Code originally designed by [mpetroff](https://github.com/mpetroff/kindle-weather-display), modified by [morganhk](https://github.com/morganhk/kindle-weather-display) before ending up here.

Features
========

Weather
-------
The Weather data is provided by Yahoo! Weather. The location of which is defined in the config (see below).

RSS
---
Latest RSS articles will be displayed under the latest weather. These RSS articles are downloaded from any RSS compliant URL which is specified in the config (see below).

Bins
----
Making the presumption that you have a dedicated Google Calendar set up with events for your Garbage collection, a warning will be shown 12 hours before your bins are due to be collected. The URL of the calendar needs to be configured in the config (see below).

Quotes
------
Every morning between midnight and midday, the latest motivational quote will be shown at the bottom of the screen (presuming there isn't a more important notification to show).

Setup
=====
The following options are configurable in the ```settings.php``` file:

Cache Period
------------
Regenerating the image upon every request is silly. Becuase of this, the data downloaded is cached. The length of time this data is valid for is configurable. This value is in seconds and the default is equivilent to half an hour.
```php
// Cache the Weather data for 1800 seconds (1/2h)
$cachePeriod = "1800";
```
Temperature format
------------------
Depending upon location and preference, the weather can be shown in either fahrenheit (F) or celsius (C).
```php
// "C" for Celsius or "F" for Fahrenheit
$tempFormat = "C";
```
Weather Location
----------------
The location of the weather data can be configured by finding the WOEID for your location.
```php
// A static location to generate data for (WOEID)
$queryLocation = "35356";
```
Live Wrapping
-------------
The news stories for the BBC are typically one line long, but some stories on other RSS feeds can be longer. To wrap stories over lines set to true.
```php
// Wrap news story lines
$lineWrap = false;
```
RSS Feed Location
-----------------
The RSS feed can be any feed that you would like displayed on the middle of the screen. This feed is updated based upon the cache period.
```php
// URL to get the RSS info from
$userRSSFeed = "http://www.happynews.com/rss/.aspx";
```
Rubbish Calendar URL
--------------------
Enter the URL of your dedicated Bin calendar to get warnings when the bins go out. Note that the title of the event is used as the on-screen warning.
```php
// User calendar feed for Bin collection etc.
$userCalRSSFeed = "https://www.google.com/calendar/feeds/CUSTOMID/full";
```
