# PayU account plugin for Magento over 1.6.0

PayU account is a web application designed as an e-wallet for shoppers willing to open an account, define their payment options, see their purchase history and manage personal profiles.

## Installation
1. Copy folders (app, lib) to the Magento root folder
2. Open Magento administration page
3. Go to the System/Cache Management and refresh all cache
4. Go to the Configuration/Sales/Payment Methods and select PayU account configuration
6. Fill in all required configuration fields:
* Merchant POS ID
* POS Auth Key
* Client ID (the same as Merchant POS ID)
* Key (MD5)
* Second key (MD5)
7. Save
