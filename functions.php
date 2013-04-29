<?php

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
      <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" media="screen" />
      <title>Status Page</title>
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


/**
 * Print the status table
 *
 */
function printStatus() {
  
  //Grab the workfile
  $siteList = getWorkFile();

  //Print out 
  print('<table class="table table-bordered table-hover" style="table-layout: fixed;">
           <tr>
		     <th style="width: 25%;">Status</th>
			 <th style="width: 50%;">Service</th>
			 <th style="width: 25%;">Load Time / Error</th></tr>');
 
  //Shift the list to skip the update time
  array_shift($siteList);

  foreach($siteList as $site) {
    
    //Determine styling of status
    if($site[3]  == 0)
      $site[3] = '<div class="alert alert-success" style="margin-bottom:0px;">OK</div>';
    else if($site[3] == 1)
      $site[3] = '<div class="alert" style="margin-bottom:0px;">Degraded</div>';
    else if($site[3] == 2)
      $site[3] = '<div class="alert alert-error" style="margin-bottom:0px;">Down</div>';
  
    //print table row
    print('<tr>' .
            '<td style="text-align:center;">' . $site[3] . '</td>' .
            '<td><a target="_blank" href="' . $site[1] . '">' . $site[0] . '</a></td>' .
			'<td>' . $site[4] . '</td>' .
          '</tr>'
    );
  }
  print('</table>');
  
}


/**
 * Run the site update sequence
 *
 */
function updateSites() {

  //Grab configuration file
  $lines = file('configuration.db');

  //Split up each line of the configuration file
  foreach($lines as $key=>$line){
   
    //Remove newlines
    $line = preg_replace('/[\n\r]/', '', $line);
  
    //Explode string into array
    $lines[$key] = explode('|',$line);
  }
   
   //Set the first entry in the file to be the update time.
   $tempString = microtime(true) . "\n";

   //Edit each line with the results
   foreach($lines as $line) {
     //Add the status to the line
     $line[3] = getStatus($line[1], $line[2]);

     //Add the line to the temp file string
     $tempString .= $line[0] . '|' . $line[1] . '|' . $line[2] . '|' . $line[3] . "\n";
   }

   //Write new contents to file
   file_put_contents('tempwork.db', $tempString);
 }

/**
 * Get the status of a particular URL
 *
 */
function getStatus($url, $checkText) {

  //Define a connection
  $conn = curl_init($url);

  //Set CURL options
  curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($conn, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($conn, CURLOPT_MAXREDIRS, 10);

  //Grab HTML
  $html = curl_exec($conn);
  
  //Remove newlines
  $html = preg_replace('/[\n\r]/', '', $html);

  //Grab HTTP response code
  $status = curl_getinfo($conn, CURLINFO_HTTP_CODE);
  $time = curl_getinfo($conn, CURLINFO_TOTAL_TIME);

  //Close the connection
  curl_close($conn);
  
  //If the status is 200 and the html contains the checkString...
  if($status  == '200' && strpos($html, $checkText) !== FALSE) {
    return('0' . '|' . $time . ' s'); //OK
  }else if($status  == '200') {
    return('1' . '|' . $time . ' s'); //Degraded
  }else {
    return('2' . '|' . 'Error: ' . $status); //Down
  }
}


/**
 * Get workfile contents
 *
 */
function getWorkFile() {
  $lines = file('tempwork.db');
  foreach($lines as $key=>$line){
    $lines[$key] = explode('|',$line);
  }

  return $lines;
}


?>
