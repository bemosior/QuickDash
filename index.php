<?php

printHeader();

printStatus();

printFooter();

/**
 * Print the status table
 *
 */
function printStatus() {

  //If the site needs to update, do so.
  if(needUpdate()){
    updateSites();
  }

  $siteList = getWorkDB();

  print('<table class="table table-bordered table-hover">
           <tr><th>Status</th><th>URL</th><th>Check String</th></tr>');
 
  //Shift the list to skip the update time
  array_shift($siteList);

  foreach($siteList as $site) {
    print('<tr>' .
            '<td style="text-align:center;">' . getStatus($site[0],$site[1]) . '</td>' .
            '<td>' . $site[0] . '</td>' .
            '<td>' . $site[1] . '</td>' .
          '</tr>'
    );
  }
}


/**
 * Check if the file needs to be updated.
 *
 */
function needUpdate() {
  $lines = getWorkDB();

  if (microtime(true) < ($lines[0][0] + 60000000) || strlen($lines[0][0]) < 5)
    return TRUE;
  else
    return FALSE; 
}


/**
 * Update sites
 *
 */
function updateSites() {
  $lines = file('configuration.db');

  //Blow up the configuration file
  foreach($lines as $key=>$line){
   
    //Remove newlines
    $line = preg_replace('/[\n\r]/', '', $line);
	
	//Explode string into array
    $lines[$key] = explode('|',$line);
  }
   
   $tempString = microtime(true) . "\n";

   //Edit each line with the results
   foreach($lines as $line) {
     //Add the status to the line
     $line[2] = getStatus($line[0], $line[1]);

     //Add the line to the temp file string
     $tempString .= $line[0] . '|' . $line[1] . '|' . $line[2] . "\n";
   }

   //Write new contents to file
   file_put_contents('tempwork.db', $tempString);
 }

/**
 * Get the status of a particular URL
 *
 */
function getStatus($url, $checkText) {

  //Define a connection to the site URL
  $conn = curl_init($url);

  //Set CURL options
  curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);

  //Grab HTML
  $html = curl_exec($conn);
  $html = preg_replace('/[\n\r]/', '', $html);

  //Grab HTTP response code
  $status = curl_getinfo($conn, CURLINFO_HTTP_CODE);

  //Close the connection
  curl_close($conn);

  //If the status is 200 and the html contains the checkString...
  if($status  == '200' && strpos($html, $checkText) !== FALSE) {
    return('<div class="alert alert-success" style="margin-bottom:0px;">OK</div>');
  }else if($status  == '200') {
    return('<div class="alert" style="margin-bottom:0px;">Impaired</div>');
  }else {
    return('<div class="alert alert-error" style="margin-bottom:0px;">Down</div>');
  }
}


/**
 * Get Work DB Contents
 *
 */
function getWorkDB() {

  $lines = file('tempwork.db');
  foreach($lines as $key=>$line){
    $lines[$key] = explode('|',$line);
  }


  return $lines;
}


/**
 * Print the Header
 *
 */
function printHeader() {

  print('
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8" />
      <meta name="description" content="" />
      <meta name="keywords" content="" />
      <meta name="author" content="" />
      <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" media="screen" />
      <title>KLN Status Page</title>
    </head>
    <body>
      <br/><br/>
      <div class="container">
  ');

}

/**
 * Print the Footer.
 * 
 */
function printFooter() {

  print('
      </div>
    </body>
    </html>
  ');

}





?>
