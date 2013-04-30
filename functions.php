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
      <script src="bootstrap/js/bootstrap.min.js"></script>
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
  $siteList = getCacheFile();

  //Print out 
  print('<table class="table table-bordered table-hover" style="table-layout: fixed;">');
 
  //Grab the time the data was last updated
  $lastUpdatedTime = $siteList[0][0];
  //Print the time the data was last updated in a "x seconds ago" format 
  print('Last updated ' . round(microtime(true) - $lastUpdatedTime) . ' seconds ago.' );
  
  //Shift the list to remove the update time from the list
  array_shift($siteList);

  foreach($siteList as $site) {
    array_shift($site);
    //Determine styling of status
    if($site[3]  == 0)
      $site[3] = '<div class="alert-message success" style="margin-bottom:0px;"><strong>OK</strong></div>';
    else if($site[3] == 1)
      $site[3] = '<div class="alert-message warning" style="margin-bottom:0px;"><strong>Degraded</strong></div>';
    else if($site[3] == 2)
      $site[3] = '<div class="alert-message error" style="margin-bottom:0px;"><strong>Down</strong></div>';
  
    //print table row
    print('<tr>' .
            '<td style="width:130px; text-align:center;">' . $site[3] . '</td>' .
            '<td><a target="_blank" href="' . $site[1] . '">' . $site[0] . '</a></td>' .
			'<td>' . $site[4] . '</td>' .
          '</tr>'
    );
  }
  print('</table>');
  
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
 * Get work file contents
 *
 */
function getCacheFile() {
  $lines = file('cache.db');
  foreach($lines as $key=>$line){
    $lines[$key] = explode('|',$line);
  }

  return $lines;
}


?>
