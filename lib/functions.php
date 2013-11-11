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
      <script>
        $(document).ready ( function(){
          $(\'.alert-message\').tooltip();
        });
      </script>

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
  print('<div id="demo" class="collapse in">
  <table class="table table-bordered table-hover" style="width: auto; margin: 0 auto !important; float: none !important;">');
 
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
  print('</table></div><!--<button type="button" class="btn btn-mini" data-toggle="collapse" data-target="#demo">Collapse Pointlessly</button>-->');
  
  //Print the time the data was last updated in a "x seconds ago" format 
  print('<p><small>Updated ' . round(microtime(true) - $lastUpdatedTime) . ' seconds ago.</small></p>' );
  
}

/**
 *  Returns the site object with the ID specified
 *
 */
function findSiteWithId($id, $siteList) {
  foreach($siteList as $site) {
    if($id == $site[0]) {
      return $site;
    }
  }
}

/**
 * Print the status table
 *
 */
function printGroupStatus() {
  
  //Grab the workfile and group configuration file
  $siteList = getCacheFile();
  $groupList = getGroupFile();
  
  //Grab the time the data was last updated
  $lastUpdatedTime = $siteList[0][0];
  
  //Shift the list to remove the update time from the list
  array_shift($siteList);
  
  //Process each group
  $groupID = 0;
  foreach($groupList as $group) {
    $groupSites = explode(',',$group[1]);
    print('<br/><button class="btn btn-primary" style="width:20%;" data-toggle="collapse" data-target="#group' . $groupID . '">' . $group[0] . '</button>');

    print('<div id="group' . $groupID . '" class="collapse');
    if($group[2] == 1)
      print(' in');
    print('"><table class="table table-bordered table-hover" style="width: auto; margin: 0 auto !important; float: none !important;">');
    foreach($groupSites as $site) {
      $site = findSiteWithId($site, $siteList);
      array_shift($site);
      
      //Determine styling of status
      $statusHTML = getStatusHTML($site[3]);
      
      print('<tr>' .
        '<td style="width:130px; text-align:center;">' . $statusHTML . '</td>' .
        '<td style="width:50%;"><a target="_blank" href="' . $site[1] . '">' . $site[0] . '</a></td>' .
        '<td style="width:130px;">' . $site[4] . '</td>' .
        '</tr>'
      );
    }
    print('</table>');
    print('</div>');
    $groupID++;
  }
  
 
  //print('</table></div><!--<button type="button" class="btn btn-mini" data-toggle="collapse" data-target="#demo">Collapse Pointlessly</button>-->');
  
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
    return '<div class="alert-message success" data-title="The service responded and contained correct data." data-placement="left"><strong>OK</strong></div>';
  else if($code == 1)
    return '<div class="alert-message warning" data-title="The service responded but contained incorrect data." data-placement="left"><strong>Degraded</strong></div>';
  else if($code == 2)
    return '<div class="alert-message error" data-title="The service did not respond." data-placement="left"><strong>Down</strong></div>';
}


/**
 * Get config file contents
 * 
 */
function getConfigFile(){

  //Grab configuration file
  $configFile = file('conf/sites.db');

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
  $lines = file('tmp/cache.db');
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
  $lines = file('conf/groups.db');
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
    $entries[$url][2] = '2' . '|' . '<a target="_blank" href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#' . //Down
        $responseCode . '">Error: ' . $responseCode . '</a>';
  }
  
  //Add the line to the temp file string
  $tempString = $entries[$url]['order'] . '|' . $entries[$url]['name'] . '|' . $url . '|' . $entries[$url]['contextString'] . '|' . $entries[$url][2] . "\n";
  //Write new contents to file
  file_put_contents('tmp/tempwork.db', $tempString, FILE_APPEND | LOCK_EX);
}


?>
