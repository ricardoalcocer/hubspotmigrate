# HubSpot to Wordpress

This short PHP script uses the HubSpot API to transfer HubSpot blog posts to Wordpress' RSS flavor, ready to import via the Wordpress admin.  The script transfers both Blog posts and their respective comments.

## Usage
* Open the **script.php** file and set the $bloguid and $accesstoken variables.  You can get this values by going to HubSpot > Settings and selecting API Access. **NOTE: HubSpot access tokens work for only 8 hours.**
* Place this file on a web-accessbile folder, for example **http://localhost**
* Run the script by browsing to **http://localhost/script.php**

## Arguments
* **Max** : Number specifying how many records to return
* **Offset** : Number specifying on which record to start

By default the script will return the topmost 10 records.  However, it appears that you cannot get more than 31 records at a time, so if you ask for 100, you'll get 31.  So if you want to return 100, perhaps you'll have to do it in four batches like:

* **Batch 1**: max=25
* **Batch 2**: max=25&offset=25
* **Batch 3**: max=25&offset=50
* **Batch 4**: max=25&offset=75

## Other
Currently only retuns data in Wordpress RSS format, but could be easily modified to return pure JSON so it can then be transferred to any system.


## Disclaimer
Distrubuited as-is with no guarantee.  I hope it works for you. 

## License
Licensed under the terms of the MIT License: alco.mit-license.org



