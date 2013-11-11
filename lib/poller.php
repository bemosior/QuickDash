<?php

require('lib/functions.php');
require('lib/RollingCurl.php');

//Get the config file entries
$entries = getConfigFile();

//URL Array
$urls = array();

//Split up each line of the configuration file
foreach($entries as $key => $line){
  
  //Add test URL to URL array
  array_push($urls, $key);
}
 
 
//Set the first entry in the file to be the update time.
$tempString = microtime(true) . "\n";

//Write new contents to file
file_put_contents('tmp/tempwork.db', $tempString, LOCK_EX);

$rc = new RollingCurl('request_callback');
$rc->window_size = 20;
foreach ($urls as $url) {
    $request = new RollingCurlRequest($url);
    $rc->add($request);
}
$rc->execute();

//Pull in completed tempfiles
$lines = file('tmp/tempwork.db');

//Remove time line
$time = array_shift($lines);

//Sort numerically by ID
array_multisort($lines, SORT_NUMERIC);

//Add time line back in
array_unshift($lines, $time);

//Publish to cache
file_put_contents('tmp/cache.db', $lines, LOCK_EX);









?>
