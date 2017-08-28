[**Wersja polska**][ext0]

# PayU account plugin for Magento 1.6.0+
``This plugin is released under the GPL license.``

**If you have any questions or issues, feel free to contact our technical support: tech@payu.pl.**

## Table of Contents

1. [Features](#features)
1. [Prerequisites](#prerequisites)
1. [Installation](#installation)
    * [Installing Manually](#installing-manually)
    * [Installing with Magento Connect](#installing-with-magento-connect)
1. [Configuration](#configuration)

## Features
The PayU payments Magento plugin adds the PayU payment option and enables you to process the following operations in your e-shop:
  * Creating a payment order (with discounts included)
  * Receive or canceling a payment order (when auto-receive is disable)
  * Conducting a refund operation (for a whole or partial order)

Moduł dodaje dwie metody płatności:
![methods][img0]
  * **Zapłać przez PayU** - przekierowanie na stronę wyboru metod płatności w PayU
  * **Zapłać kartą** - bezpośrednie przekierowanie na formularz płatności kartą

# #Prerequisites

**Important:** This plugin works only with REST API (checkout) points of sales (POS).

The following PHP extensions are required:

  * [cURL][ext2] to connect and communicate to many different types of servers with many different types of protocols.
  * [hash][ext3] to process directly or incrementally the arbitrary length messages by using a variety of hashing algorithms.

## Installation

### Option 1
**Przeznaczona dla użytkowników z dostępem poprzez FTP do instalacji Magento**

1. Pobierz moduł z [repozytorium GitHub][ext3] jako plik zip
1. Rozpakuj pobrany plik
1. Połącz się z serwerem ftp i skopiuj katalogi `app`, `lib` oraz `skin` z rozpakowanego pliku do katalogu głównego swojego sklepu Magento
1. W celu aktualizacji listy dostępnych wtyczek należy wyczyścić cache:
    * Przejdź do strony administracyjnej swojego sklepu Magento [http://shop-url/admin].
    * Przejdź do **System** > **Cache Management**.
    * Naciśnij przycisk **Flush Magento Cache**.
1. Jeżeli używasz opcji kompilacji po przejściu do **System** > **Tools** > **Compilation** należy nacisnąć przycisk **Run Compilation Process**.

### Option 2
**Z użyciem Magento Connect**

1. Przejdź do strony administracyjnej swojego sklepu Magento [http://shop-url/admin].
1. Przejdź do **System** > **Magento Connect** > **Magento Connect Manager**.
1. W sekcji **Install New Extensions section** do pola `Paste the extension key to install` należy wkleić `http://connect20.magentocommerce.com/community/PayU_Account` i wcisnąć przycisk `Install`
1. Po chwili pojawi się informacji o wtyczce. W celu instalacji należy nacisnąć przycisk `Proceed`

### Option 3
**Z użyciem skryptu modman**

Moduł PayU zawiera konfigurację umożliwiającą instalację poprzez skrypt `modman`.
W celu instalcji z użyciem `modman` proszę skozystać z dokumentacji skryptu `modman`.

## Configuration

Independently of the installation method, the configuration looks the same:

1. Go to the Magento administration page [http://shop-url/admin].
2. Go to **System** > **Configuration** window.
3. From the **Configuration** menu on the left, in the **Sales** section, select **Payment Methods**.
4. In the list of available methods, click PayU to expand the configuration form, and specify the [configuration parameters](#configuration).
5. Click ![save_config][img2] in the top right corner of the page.

### Configuration Parameters

#### Main parameters

| Parameter | Description |
|---------|-----------|
| Enable plugin? | Określa czy metoda płatności będzie dostępna w sklepie na liście płatności. |

#### POS parameters

| Parameter | Description |
|---------|-----------|
|POS ID|Unique ID of the POS|
|Second Key|MD5 key for securing communication|
|OAuth - client_id|client_id for OAuth|
|OAuth - client_secret|client_secret for OAuth|

<!--LINKS-->

<!--topic urls:-->

<!--external links:-->
[ext0]: README.EN.md
[ext1]: https://github.com/PayU/plugin_magento_160
[ext2]: http://php.net/manual/en/book.curl.php
[ext3]: http://php.net/manual/en/book.hash.php

<!--images:-->
[img0]: readme_images/methods.png
