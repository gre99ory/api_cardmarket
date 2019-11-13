# MKM API PLUG-IN
The MKM API plugin for WordPress is designed to receive information about seller’s orders by API (https://api.cardmarket.com). To receive the data correctly, you need to create an application in your personal account and receive it (APP NAME, APP TOKEN, APP SECRET, ACCESS TOKEN, ACCESS TOKEN SECRET).


# Files

|       file     |description                         |
|----------------|------------------------------------|
|index.php           |`The main file of the plugin. Contains all the function.`                 |
|uninstall.php       |`Plugin removal functionality`                 |
|css/admin_style.css |`Cascading Style Sheets`     |
|js/admin_scripts.js |`JavaScript scripts`     |
|js/admin_scripts.js |`JavaScript scripts`     |

# How to use the plugin

1. After installing and activating the plugin, you should create a new application in the plugin settings panel. To do this, fill in (APP NAME, APP TOKEN, APP SECRET, ACCESS TOKEN, ACCESS TOKEN SECRET).
2. Also, when creating a new application, you need to specify the time interval with which the plugin will update data on orders by API
3. When you click the "Get all data" button, data is filled in for the created application
4. When you click on the "Get All Data" button, you can update the API data for orders with statuses (Sent, Paid, and Purchased). The same data comes in the time interval specified in the "Interval" settings.


# Latest Versions
* **1.0.6** - Added README.md file and comments for functions
* **1.0.5** - Correction of errors receiving data on the cron