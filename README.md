# PayU account plugin for Magento over 1.6.0
-------
PayU account is a web application designed as an e-wallet for shoppers willing to open an account, 
define their payment options, see their purchase history and manage personal profiles.

## Dependencies

The following PHP extensions are required:

* cURL
* hash
* XMLWriter
* XMLReader

## Installation

There are two ways in which you can install the plugin:

* [manual][6]
* [Magento Connect][7]

See the sections below to find out about steps for each of the procedures.

### Installing Manually

To install the plugin manually, simply copy the folders and clean the cache:

1. Copy the folders from [plugin_magento_160](https://github.com/PayU/plugin_magento_160) to the Magento root folder on the server.
2. In order to update the list of available plugins, clean the cache:
  * Go to Magento administration page [http://your-magento-url/admin].
  * Go to **System** > **Cache Management**.
  * Select all cache types and click  the **Flush Magento Cache** button.<br /> 
  **Note:** If this doesnt work, flush other cache as well.
  * ![cache_flush_cusscess][8] button.

### Installing with Magento Connect 

You need to register to get an account and the token.

1. Go to **System** > **Magento Connect** > **Magento Connect Manager**.
2. In the Install New Extensions section, click Search for modules via Magento Connect.
3. Use the search box to find PayU.
4. Click the PayU icon to get to the installation page and click the ![save config][5] button.<br /> 
**Note:** You must be registered and logged in to get the extension key.

## Configuration

Independently from how you installed the plugin, the configuration looks the same:

1. Go to Magento administration page [http://your-magento-url/admin].
2. Go to **System** > **Configuration** window. 
3. From the Configuration menu on the left, in the Sales section, select Payment Methods.
4. In the list of available methods, click PayU to expand the configuration form. Fill in all the required configuration fields:


----
external sites:
[1]:
[2]: https://raw.github.com/PayU/doc_draft/master/2.png?login=openpayu&token=54514e8b7d4b8d5cbab225da48bb59ec
[3]:
[4]:
[5]: https://raw.github.com/PayU/doc_draft/master/save_config.png?login=openpayu&token=ff3be9f01987512c6a47fdfccaefc927
[6]: https://github.com/PayU/doc_draft/blob/master/README.md#manual-installation
[7]: https://github.com/PayU/doc_draft/blob/master/README.md#magento-connect

images:

[8]: 
[8]: