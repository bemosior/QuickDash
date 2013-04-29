<?php

require('functions.php');
require('RollingCurl.php');

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
file_put_contents('tempwork.db', $tempString, LOCK_EX);

$rc = new RollingCurl('request_callback');
$rc->window_size = 20;
foreach ($urls as $url) {
    $request = new RollingCurlRequest($url);
    $rc->add($request);
}
$rc->execute();

$lines = file('tempwork.db');
$time = array_shift($lines);
array_multisort($lines, SORT_NUMERIC);
array_unshift($lines, $time);
file_put_contents('tempwork.db', $lines, LOCK_EX);

/**
 * Request Callback for cURL requests
 * 
 */
function request_callback($html, $info, $request) {
  //Remove newlines
  $html = preg_replace('/[\n\r]/', '', $html);
  $url = array_shift(get_object_vars($request));
  $time = array_shift(array_slice($info,8,1));
  $responseCode = array_shift(array_slice($info,2,1));
  $entries = getConfigFile();
  
  //Determine response
  if($responseCode == '200' && strpos($html, $entries[$url]['contextString']) !== FALSE) {
    $entries[$url][2] = '0' . '|' . $time . ' s'; //OK
  }else if($responseCode  == '200') {
    $entries[$url][2] = '1' . '|' . $time . ' s'; //Degraded
  }else {
    $entries[$url][2] = '2' . '|' . 'Error: ' . $responseCode; //Down
  }
  
  //Add the line to the temp file string
  $tempString = $entries[$url]['order'] . '|' . $entries[$url]['name'] . '|' . $url . '|' . $entries[$url]['contextString'] . '|' . $entries[$url][2] . "\n";
  //Write new contents to file
  file_put_contents('tempwork.db', $tempString, FILE_APPEND | LOCK_EX);
}





?>
