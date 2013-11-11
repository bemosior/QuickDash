QuickDash
=========

A PHP web dashboard that displays a simple status message about web services based on the HTTP response code and HTML content. QuickDash is a great starting point for building a dashboard page and reasonably easy to install.

Take a quick look at the [demo site](https://bmosior.com/qd).

##Help
QuickDash needs improvement! Find a bug? Think a new feature would fit well? Add an issue or pull request!

##Test/Dev Environment:
* Linux (CentOS 6)
* Apache 2.2
* PHP 5.3
* Curl

Your mileage may vary.

##Installation
1. Navigate to your web folder (on CentOS, typically /var/www/html)
2. Grab the QuickDash files:
  * With Git:
    ```git clone https://github.com/bemosior/QuickDash.git```
  * Without Git: 
     * Download the ZIP file from the QuickDash GitHub repo page.
     * Create a directory in your web folder, like "QuickDash".
     * Extract the ZIP into the newly created directory.
3. Modify 'conf/sites.db' with one entry per line in the following format: ```ID|Name|URL|ContextString```, where "ID" is the numeric, sequential unique ID of the entry, "Name" is the display name of the web service, "URL" is the URL to check, and "Context" is a bit of HTML code that must be contained in the checked page. An example entry is included. 
  * To skip the ContextString matching, use a space as the ContextString. That should handle most cases, but I'm aware it isn't ideal (future issue).
4. Configure Apache to block access to poller.php:
```<Files lib/poller.php>
  Order allow,deny
  Deny from all
</Files>```
5. Configure the poller cron: ```crontab -e``` ```* * * * * cd /your/html/directory/QuickDash ; /usr/bin/php lib/poller.php```
6. Make sure 'tmp/tempwork.db' and 'tmp/cache.db' files are writeable by the user running the crontab.  ```chown thecronuser tmp/tempwork.db``` where "thecronuser" is the user under which the cronjob is configured.

Ta-da.

##General Information
* Updates are run regularly with the cron and cached in a file for retrieval.
* A service is deemed "OK" if the HTTP status code is '200' and the HTML contains the context string.
* A service is deemed "Degraded" if the HTTP status code is '200' and the HTML does **not** contain the context string.
* A service is deemed "Down" if the HTTP status code is not '200' (redirects are followed, eventually resulting in a 200).
* For simplicity, SSL certificates are simply accepted (no validation) by Curl (with 'https' URLs).

##Groups
Basic group functionality has been added. To enable, edit index.php, comment out getStatus(), and uncomment getGroupStatus().

Configure 'conf/groups.db' with one group per line in the following format: ```Name|Members|Collapse```, where "Name" is the group's display name, "Members" is a comma-separated list (no spaces) of site IDs (from the conf/sites.db file), and "Collapse" is either 0 (load collapsed) or 1 (load uncollapsed).