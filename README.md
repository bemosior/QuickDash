QuickDash
=========

An easily installed PHP web dashboard that displays a simple status message about web services based on HTTP response code and HTML content.

Take a quick look at the [Demo](https://bmosior.com/qd).

##Help!
QuickDash isn't perfect! Find a bug? Think a feature would fit well? File an issue or pull request!

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
4. Make sure 'tempwork.db' is writeable by the webserver ```chmod oug+rw tempwork.db```

Ta-dah.

##What's going on?
* The 'configuration.db' file sets the web services to poll. 
* Updates are user-triggered (when the page is visited), at a maximum of 1 update per 30 seconds.
* A service is deemed "OK" if the HTTP status code is '200' and the HTML contains the context string.
* A service is deemed "Degraded" if the HTTP status code is '200' and the HTML does **not** contain the context string.
* A service is deemed "Down" if the HTTP status code is not '200' (redirects are followed, eventually resulting in a 200).
* For simplicity, SSL certificates are simply accepted (no validation) by Curl (with 'https' URLs).
