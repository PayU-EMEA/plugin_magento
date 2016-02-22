## 2.1.10
 * Replace http to https for static resources

## 2.1.9
 * Add lang to redirectUri

## 2.1.3
 * microtime() added to extOrderId to avoid ORDER_NOT_UNIQUE error
 * fixed status update bug
 * API version fixed
 * not used status updates removed
 * _payuPayMethod from CARD to CARD_TOKEN changed

## 2.1.2
 * SDK version 2.1.2 included (SSL3 protocol dissabled)

## 2.1.1
* API 2.1 compatible

## 2.0.2
* Rounding numbers fixed

## 2.0.1
* Refund functionality added
* Added discounted price calculation
* SDK 2.0.0 compatible
* Fixed Self-return flow
* Fix for coupon total amount and order summary
* Fix for user rights and Accept/Cancel buttons


## 1.8.2
* Changed order's statuses management
* Fixed payment acceptance
* Fixed shipping taxes

## 1.8.1
* Fixed GrandTotal Amount to SubTotal
* Fixed updatePaymentStatusCompleted for Self-Returns

## 1.8.0
* SDK 1.9.2 compatible
* Fixed PayU order cancelling
* Fixed adding customer shipping address in orders that are not virtual
* Fixed status changing for Payment Review after complete payment
* Fixed updating customer data
* Changed order number in  PayU description

## 1.7.0
* Fixed problem with accepting and cancelling order in PayU [Issue #6](https://github.com/PayU/plugin_magento_160/issues/6)
* Removed PayU.php file
* Changed type of license
* Added license file

## 0.1.6.5.1
* Fixed Email empty value in new order
* Changed description labels in configuration
* Updated order statuses list

## 0.1.6.5
* Added customer and shipping information in order create

## 0.1.6.4
* Fixed displaying a message when you add item to cart
* Fixed advertisements localization
* Added redirect after payment cancel

## 0.1.6.3.2
* Fixed empty ShippingCostList in Checkout process

## 0.1.6.3.1
* Fixed order status changes for Completed

## 0.1.6.3
* Changed shopping process flow without authentication before summary
* Fixed billing address for virtual order

## 0.1.6.2
* Fixed shipping costs list for virtual order
* Fixed product tax rates
* Fixed order grand total value

## 0.1.6.1
* Changed extension name: PayU_Account was PayU_PayU
