[**Wersja polska**][ext0]

# PayU account plugin for Magento 1.6.0+
``This plugin is released under the GPL license.``

**If you have any questions or issues, feel free to contact our [technical support][ext7].**

* If you have Magneto in version 2.x please use [plugin for ver 2.x][ext6]

## Table of Contents

1. [Features](#features)
1. [Prerequisites](#prerequisites)
1. [Installation](#installation)
1. [Configuration](#configuration)
    * [Parameters](#parameters)

## Features
The PayU payments Magento plugin adds the PayU payment option and enables you to process the following operations in your e-shop:
  * Creating a payment order (with discounts included)
  * Receive or canceling a payment order (when auto-receive is disable)
  * Conducting a refund operation (for a whole or partial order)

The module adds two payment methods:

![methods][img0]
  * **Pay with PayU** - redirection to PayU hosted paywall (with all available payment methods)
  * **Pay by card** - redirection to PayU hosted card payment form

# Prerequisites

**Important:** This plugin works only with REST API (checkout) points of sales (POS). If you do not already have PayU merchant account, [**please register in Production**][ext4] or [**please register in Sandbox**][ext5]

The following PHP extensions are required:

  * [cURL][ext2] to connect and communicate to many different types of servers with many different types of protocols.
  * [hash][ext3] to process directly or incrementally the arbitrary length messages by using a variety of hashing algorithms.

## Installation

### Option 1
**Intended for users with FTP access to Magento Installation**

1. Download the module from [repozytorium GitHub][ext3] as zip file.
1. Unzip the file.
1. Connect with your FTP server and copy `app`, `lib` and `skin` directories from the unzipped file to the main directory of your Magento installation.
1. Flush the cache to update the available plugins list:
    * Go to admin page of your Magento installation [http://shop-url/admin].
    * Choose **System** > **Cache Management**.
    * Click **Flush Magento Cache** button.

### Option 2
**Using modman script**

PayU module includes configuration which makes it possible to install via `modman` script.
Please refer to `modman` documentation for further details.

#### WARNING
If you are using compilation option, choose **System** > **Tools** > **Compilation** and click **Run Compilation Process** button.

Additionally, if you update the module from an older version, you must delete from `includes/src` directory `OpenPayu` directory and all files that start with `OpenPayU`

## Configuration

Independently of the installation method, the configuration looks the same:

1. Go to the Magento administration page [http://shop-url/admin].
2. Go to **System** > **Configuration**.
3. From the **Configuration** menu on the left, in the **Sales** section, select **Payment Methods**.
4. In the list of available methods, click **PayU** or **PayU - cards** to expand the configuration form.
5. Click `Save config`.

### Parameters

#### Main parameters

| Parameter | Description |
|---------|-----------|
| Enable plugin? | Defines the availability of the payment method on the payment method list during checkout. |
| Sandbox mode | Defines if payments will be created in PayU sandbox instead of production (live) environment. |

#### POS parameters

| Parameter | Description |
|---------|-----------|
|POS ID|Unique ID of the POS|
|Second Key|MD5 key for securing communication|
|OAuth - client_id|client_id for OAuth|
|OAuth - client_secret|client_secret for OAuth|

#### POS parameters - Sandbox mode
Available when parameter `Sandbox mode` is set to `Yes`.

| Parameter | Description |
|---------|-----------|
|POS ID|Unique ID of the POS|
|Second Key|MD5 key for securing communication|
|OAuth - client_id|client_id for OAuth|
|OAuth - client_secret|client_secret for OAuth|


<!--LINKS-->

<!--topic urls:-->

<!--external links:-->
[ext0]: README.md
[ext1]: https://github.com/PayU/plugin_magento_160
[ext2]: http://php.net/manual/en/book.curl.php
[ext3]: http://php.net/manual/en/book.hash.php
[ext4]: https://www.payu.pl/en/commercial-offer
[ext5]: https://secure.snd.payu.com/boarding/#/form&pk_campaign=Plugin-Github&pk_kwd=Magento
[ext6]: https://github.com/PayU/plugin_magento_2
[ext7]: https://www.payu.pl/en/help

<!--images:-->
[img0]: readme_images/methods_en.png
