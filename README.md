# PayU account plugin for Magento over 1.6.0
-------
``This plugin is released under the GPL license.``

PayU account is a web application designed as an e-wallet for shoppers willing to open an account, 
define their payment options, see their purchase history, and manage personal profiles.

## Table of Contents

[Prerequisites][1] <br />
[Installation][2]
* [Installing Manually][2.1]
* [Installing with Magento Connect][2.2]

[Configuration][3]
* [Configuration Parameters][3.1]


## Prerequisites

**Important:** This plugin works only with checkout points of sales (POS).

The following PHP extensions are required:

* [cURL][ext2] to connect and communicate to many different types of servers with many different types of protocols.
* [hash][ext3] to process directly or incrementally the arbitrary length messages by using a variety of hashing algorithms.
* [XMLWriter][ext4] to wrap the libxml xmlWriter API.
* [XMLReader][ext5] that acts as a cursor going forward on the document stream and stopping at each node on the way.

## Installation

There are two ways in which you can install the plugin:

* [manual installation][2.1] by copying and pasting folders from the repository
* [Magento Connect installation][2.2] from the administration page

See the sections below to find out about steps for each of the procedures.

### Installing Manually

To install the plugin manually, simply copy folders and refresh the list of plugins:

1. Copy the folders from [the plugin repository][ext1] to your Magento root folder on the server.
2. In order to update the list of available plugins, clean the cache:
  * Go to the Magento administration page [http://your-magento-url/admin].
  * Go to **System** > **Cache Management**.
  * Select all cache types and click  the **Flush Magento Cache** button.<br /> 
  **Note:** If the list of plugins doesn't refresh, flush other cache as well.

      **Results**<br />
     ![cache_flush_cusscess][img3]

### Installing with Magento Connect 

The Mangento Connect tool allows you to install the plugin from the administration page. 

**Before you start**<br />
It is recommended to always backup your installation prior to use.

1. Go to Magento administration page [http://your-magento-url/admin].
2. Go to **System** > **Magento Connect** > **Magento Connect Manager**.
3. In the Install New Extensions section, click Search for modules via Magento Connect.<br /> 
*Option:* Paste the extension key to install.
4. Use the search box to find PayU.
5. Click the **PayU** icon and install the plugin by clicking the **Install Now** button.<br />
**Note:** If you are new to Magento Connect, when you click **Install Now** you are asked to register and log in to get the extension key.<br />
  
## Configuration

Independently of the installation method, the configuration looks the same:

1. Go to the Magento administration page [http://your-magento-url/admin].
2. Go to **System** > **Configuration** window. 
3. From the **Configuration** menu on the left, in the **Sales** section, select **Payment Methods**.
4. In the list of available methods, click PayU to expand the configuration form, and specify the [configuration parameters][3.1].
5. Click ![save_config][img2] in the top right corner of the page.

### Configuration Parameters

The tables below present the descriptions of the configuration form parameters.

#### Main parameters

The main parameters for plugin configuration are as follows:

| Parameter | Values | Description | 
|:---------:|:------:|:-----------:|
|Enabled|Yes/No|Specifies whether the module is enabled.|
|OneStepCheckout Enabled|Yes/No|Specifies whether buying from the cart via Payu is enabled.|
|Self-Return Enabled|Yes/No|If self-return is disabled, the payment must be confirmed manually.|
|New Order Status|Pending/Processing/Complete/ <br /> Closed/Canceled/On Hold|Defines which status is assigned to new orders. By deafult, the *Processing* status is assigned to each new order.|
|Order Validity Time|24h/12h/6h/1h/30min|Specifies the time during which the order is valid in the PayU system. When the validity time expires, the order is cancelled, and you are notified that the transaction failed.|
|Test Mode On|Yes/No|If you are in the test mode, the transactions are only simulated and no real payments are made. Use the test mode to see how the transactions work.|

#### Parameters of production and test environments

The test environment is called *Sandbox* and you can adjust it separately from the production environment to see which configuration suits you best.
To check the values of the parameters below, go to **Administration Panel** > **My shops** > **Your shop** > **POS** and click the name of a given POS.

**Important:** If you set the [**Test Mode On**][3.1.1] parameter to *Yes*, the transactions in your shop are only simulated. No real payments are made.

| Parameter | Description | 
|:---------:|:-----------:|
|POS ID|Unique ID of the POS|
|Key|Unique MD5 key
|POS Auth Key|Transaction authorization key|
|Second Key| MD5 key for securing communication|

#### Settings of external resources

You can set external resources for the following:

| Parameter |Description | 
|:---------:|:-----------:|
|OneStepCheckout button|URL address of the button image for OneStepCheckout|
|Small logo|URL address of the logo image that is visible in the list of payment methods|
|PayU advertisement|URL address of the PayU advertisement for your page|

<!--LINKS-->

<!--topic urls:-->

[1]: https://github.com/PayU/plugin_magento_160#prerequisites
[2]: https://github.com/PayU/plugin_magento_160#installation
[2.1]: https://github.com/PayU/plugin_magento_160#installing-manually
[2.2]: https://github.com/PayU/plugin_magento_160#installing-with-magento-connect
[3]: https://github.com/PayU/plugin_magento_160#configuration
[3.1]: https://github.com/PayU/plugin_magento_160#configuration-parameters
[3.1.1]: https://github.com/PayU/plugin_magento_160#main-parameters
[3.1.2]: https://github.com/PayU/plugin_magento_160#parameters-of-production-and-test-environments
[3.1.3]: https://github.com/PayU/plugin_magento_160#settings-of-external-resources


<!--external links:-->

[ext1]: https://github.com/PayU/plugin_magento_160
[ext2]: http://php.net/manual/en/book.curl.php
[ext3]: http://php.net/manual/en/book.hash.php
[ext4]: http://php.net/manual/en/book.xmlwriter.php
[ext5]: http://php.net/manual/en/book.xmlreader.php

<!--images:-->

[img2]: https://raw.github.com/PayU/plugin_magento_160/master/readme_images/save_config.png
[img3]: https://raw.github.com/PayU/plugin_magento_160/master/readme_images/cache_flushed.png