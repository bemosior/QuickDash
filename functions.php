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
      <title>QuickDash</title>
    </head>
    <body>
      <script src="bootstrap/js/jquery.min.js"></script>
      <script src="bootstrap/js/bootstrap.min.js"></script>
      <br/><br/>
      <div class="container">
      <h1>QuickDash</h1>
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
  $siteList = getCacheFile();

  //Print out 
  print('<table class="table table-bordered table-hover" style="width: auto; margin: 0 auto !important; float: none !important;">');
 
  //Grab the time the data was last updated
  $lastUpdatedTime = $siteList[0][0];
  
  //Shift the list to remove the update time from the list
  array_shift($siteList);

  foreach($siteList as $site) {
    array_shift($site);
    
    //Determine styling of status
    $statusHTML = getStatusHTML($site[3]);

    //print table row
    print('<tr>' .
            '<td style="width:130px; text-align:center;">' . $statusHTML . '</td>' .
            '<td><a target="_blank" href="' . $site[1] . '">' . $site[0] . '</a></td>' .
            '<td style="width:130px;">' . $site[4] . '</td>' .
          '</tr>'
    );
  }
  print('</table>');
  
  //Print the time the data was last updated in a "x seconds ago" format 
  print('<p><small>Updated ' . round(microtime(true) - $lastUpdatedTime) . ' seconds ago.</small></p>' );
  
}


/**
 * Determine status HTML based on status code
 * 
 */
function getStatusHTML($code) {
  //0 is OK
  //1 is Degraded
  //2 is Down
  if($code  == 0)
    return '<div class="alert-message success"><strong>OK</strong></div>';
  else if($code == 1)
    return '<div class="alert-message warning"><strong>Degraded</strong></div>';
  else if($code == 2)
    return '<div class="alert-message error"><strong>Down</strong></div>';
}


/**
 * Get config file contents
 * 
 */
function getConfigFile(){

  //Grab configuration file
  $configFile = file('configuration.db');

  //Entry array
  $entries = array();

  //Split up each line of the configuration file
  foreach($configFile as $key=>$line){
 
    //Remove newlines
    $line = preg_replace('/[\n\r]/', '', $line);

    
    //Explode string into array
    $configArray = explode('|',$line);
    $entries[$configArray[2]] = array(
                                'name' => $configArray[1],
                                'contextString' => $configArray[3],
                                'order' => $configArray[0]
                              );
  }
  
  return $entries;
}


/**
 * Get cache file contents
 *
 */
function getCacheFile() {
  $lines = file('cache.db');
  foreach($lines as $key=>$line){
    $lines[$key] = explode('|',$line);
  }

  return $lines;
}

/**
 * Get group file contents
 *
 */
function getGroupFile() {
  $lines = file('groups.db');
  foreach($lines as $key=>$line){
    $lines[$key] = explode('|',$line);
  }

  return $lines;
}


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
    $entries[$url][2] = '0' . '|' . 'Time: ' . $time . ' s'; //OK
  }else if($responseCode  == '200') {
    $entries[$url][2] = '1' . '|' . 'Time: ' . $time . ' s'; //Degraded
  }else {
    $entries[$url][2] = '2' . '|' . 'Error: ' . $responseCode; //Down
  }
  
  //Add the line to the temp file string
  $tempString = $entries[$url]['order'] . '|' . $entries[$url]['name'] . '|' . $url . '|' . $entries[$url]['contextString'] . '|' . $entries[$url][2] . "\n";
  //Write new contents to file
  file_put_contents('tempwork.db', $tempString, FILE_APPEND | LOCK_EX);
}


?>
