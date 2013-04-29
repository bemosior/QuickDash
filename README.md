QuickDash
=========

A PHP web dashboard that displays a simple status message about web services based on HTTP response code and HTML content. QuickDash is a great starting point for building a dashboard page and really easy to install.

Take a quick look at the [demo site](https://bmosior.com/qd).

##Help
QuickDash needs improvement! Find a bug? Think a feature would fit well? Add an issue or file a pull request!

##Test Environment:
* Linux (CentOS)
* Apache
* PHP5
* Curl

Your mileage may vary.

##Installation
1. Navigate to your web folder (on CentOS, typically /var/www/html)
2. Grab the files:
  * With Git:
    ```git clone https://github.com/bemosior/QuickDash.git```
  * Without Git: 
     * Download the ZIP file from the QuickDash GitHub repo page.
     * Create a directory in your web folder, like "QuickDash".
     * Extract the ZIP into the newly created directory.
3. Modify 'configuration.db' with one entry per line in the following format: ```Name|URL|ContextString```, where "Name" is the display name of the web service, "URL" is the URL to check, and "Context" is a bit of HTML code that must be contained in the checked page. An example entry is included. 
  * To skip the ContextString matching, use a space as the ContextString. That should handle most cases, but I'm aware it isn't ideal (future issue).
4. Configure the poller cron: ```crontab -e``` ```* * * * * cd /your/html/directory/QuickDash ; /usr/bin/php poller.php```
5. Make sure 'tempwork.db' is writeable by the user making the changes.  ```chown thecronuser tempwork.db``` where "thecronuser" is the user under which the cronjob is configured.

Ta-da.

##What's going on?
* The 'configuration.db' file sets the web services to poll. 
* Updates are run regularly with the cron and cached in a file for retrieval.
* A service is deemed "OK" if the HTTP status code is '200' and the HTML contains the context string.
* A service is deemed "Degraded" if the HTTP status code is '200' and the HTML does **not** contain the context string.
* A service is deemed "Down" if the HTTP status code is not '200' (redirects are followed, eventually resulting in a 200).
* For simplicity, SSL certificates are simply accepted (no validation) by Curl (with 'https' URLs).

