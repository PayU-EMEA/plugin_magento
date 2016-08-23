# PayU account plugin for Magento 1.6.0+
-------
``This plugin is released under the GPL license.``

**If you have any questions or issues, feel free to contact our technical support: tech@payu.pl.**

## Table of Contents

1. [Features](#features)
1. [Prerequisites](#prerequisites)
1. [Installation](#installation)
    * [Installing Manually](#installing-manually)
    * [Installing with Magento Connect](#installing-with-magento-connect)
1. [Configuration](#configuration)

##Features
The PayU payments Magento plugin adds the PayU payment option and enables you to process the following operations in your e-shop:

* Creating a payment order (with discounts included)
* Receive or canceling a payment order (when auto-receive is disable)
* Conducting a refund operation (for a whole or partial order)

##Prerequisites

**Important:** This plugin works only with REST API (checkout) points of sales (POS).

The following PHP extensions are required:

* [cURL][ext2] to connect and communicate to many different types of servers with many different types of protocols.
* [hash][ext3] to process directly or incrementally the arbitrary length messages by using a variety of hashing algorithms.

##Installation

There are two ways in which you can install the plugin:

* [manual installation](#installing-manually) by copying and pasting folders from the repository
* [Magento Connect installation](#installing-with-magento-connect) from the administration page

See the sections below to find out about steps for each of the procedures.

###Installing Manually

To install the plugin manually, simply copy folders and refresh the list of plugins:

1. Copy the folders from [the plugin repository][ext1] to your Magento root folder on the server.
1. In order to update the list of available plugins, clean the cache:
  * Go to the Magento administration page [http://your-magento-url/admin].
  * Go to **System** > **Cache Management**.
  * Select all cache types and click  the **Flush Magento Cache** button.<br /> 
  **Note:** If the list of plugins doesn't refresh, flush other cache as well.

  **Results**<br />  ![cache_flush_cusscess][img3]<br />
3. If you have enabled compilation **System** > **Tools** > **Compilation** you have to click **Run Compilation Process**. 

###Installing with Magento Connect 

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
  
##Configuration

Independently of the installation method, the configuration looks the same:

1. Go to the Magento administration page [http://your-magento-url/admin].
2. Go to **System** > **Configuration** window. 
3. From the **Configuration** menu on the left, in the **Sales** section, select **Payment Methods**.
4. In the list of available methods, click PayU to expand the configuration form, and specify the [configuration parameters](#configuration).
5. Click ![save_config][img2] in the top right corner of the page.

### Configuration Parameters

The tables below present the descriptions of the configuration form parameters.

#### Main parameters

The main parameters for plugin configuration are as follows:

| Parameter | Values | Description | 
|:---------:|:------:|:-----------:|
|Enabled|Yes/No|Specifies whether the module is enabled.|

#### POS parameters

To check the values of the parameters below, go to **Administration Panel** > **My shops** > **Your shop** > **POS** and click the name of a given POS.

| Parameter | Description | 
|:---------:|:-----------:|
|POS ID|Unique ID of the POS|
|Second Key|MD5 key for securing communication|
|OAuth - client_id|client_id for OAuth|
|OAuth - client_secret|client_secret for OAuth|

<!--LINKS-->

<!--topic urls:-->

<!--external links:-->

[ext1]: https://github.com/PayU/plugin_magento_160
[ext2]: http://php.net/manual/en/book.curl.php
[ext3]: http://php.net/manual/en/book.hash.php

<!--images:-->

[img2]: https://raw.github.com/PayU/plugin_magento_160/master/readme_images/save_config.png
[img3]: https://raw.github.com/PayU/plugin_magento_160/master/readme_images/cache_flushed.png
