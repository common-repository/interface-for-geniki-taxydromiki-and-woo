=== Interface for Geniki Taxydromiki API v2 and Woo ===
Plugin URI: https://web-panda.gr/
Description: Interface between Woocommerce and Geniki Taxydromiki web service API (v2)
Version: 1.0.1
Author: Web-Panda.gr
Author URI: https://web-panda.gr/
Contributors: kiiraklis94
Tags: ecommerce, e-commerce,  wordpress ecommerce, shipping, woocommerce, geniki taxydromiki
Requires at least: 4.0
Tested up to: 6.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Interface for Geniki Taxydromiki API v2 and Woocommerce.

== Description ==

This plugin is inspired by an older (and no longer in active development) plugin that can be found [here](https://wordpress.org/plugins/woo-and-geniki-taxydromiki-api-interface/).

I have updated the main functionality of the plugin to support the newest versions of wordpress and woocommerce, and I have added new features. The plugin now also uses the latest (v2) version of Geniki Taxidromiki's API.



=== Features: ===

* Provides interface with Geniki Taxydromiki web service API (v2). Description of this API can be found [here](https://voucher.taxydromiki.gr/help/jobservicesapiv2.pdf).
  
* You can generate vouchers manually for each order or enable the option to create the vouchers automatically when a new order is made.

* Order weight is calculated automatically.

* Adds metabox that shows you the voucher's information, and allows you to take actions regarding the voucher.

* Cancel the voucher using the "Cancel Voucher" button.

* Re-generate a voucher for an order (for example, if you changed the order information). This cancels the previous voucher and creates a new one for the order.

* You can finalize/close the voucher by pressing the "Finalize/Close Voucher" button. 

* Skips voucher generation when shipping method is Local Pickup.
 
* Provides Voucher PDF for printing. You can chose whether the format will be Flyer (A4 paper) or Sticker in the plugin's settings.

* A column on the Woocommerce Orders table allows you to easily see and print the generated voucher.

* Automatically adds Cash on Delivery (AM) service when the payment method is cash on delivery.

* Automatically adds "Εμπορευματική Μεταφορά" (ΦΡ) service when country is Cyprus.

* Test mode that allows you to take the necessary actions required by Geniki Taxydromiki, before receiving live/production credentials.

* The plugin adds order notes for everything it does. Error Codes (should something go wrong) also appear in your order notes for easier debugging.

* Finalizing/Closing an order makes the order metabox and the print button on the orders list column green so you can tell the voucher status just by looking.

* Print (pdf) all vouchers for orders between specified dates (3 vouchers per page or 1 voucher per page).

* Bulk actions in order list page to generate and print all vouchers for selected orders.

* Track and Trace shortcode to add order tracking directly to your website( [ifgtapifwoo-track-and-trace] ).



== Frequently Asked Questions == 

= Does it work? =
Yes. Just use the credentials provided by Geniki Taxydromiki.

= What is "Test Mode"? =
Geniki Taxydromiki requires a testing procedure before giving you access to a live/production API. Using this plugin's Test Mode, and by just completing a test order, all the required functions are carried out. Don't forget to let Geniki Taxydromiki know you've done this so they'll give you the production API credentials.

= What is the "Cancel Voucher" button? =
This button makes an API call to the Geniki Taxydromiki API to cancel the currently generated voucher. You can re-enable/un-cancel the voucher once you've cancelled it.


= I changed some order information (eg: at the customer's request). What should I do to reflect these changes on the voucher? =
Just click on the "Regenerate Voucher" button. This cancells the current voucher and generates a new one.

Don't forget to update the Order before doing this.

= I clicked on "Finalize Order" and now I need to make some changes. Is this possible? =
Per Geniki Taxydromiki's instructions, the order should be finalized ONLY at the stage when no more changes can be made to the order. Once you click the "Finalize" button, you won't be able to Re-Generate or Cancel the current voucher. 

= How do I get live/production credentials from Geniki Taxydromiki? =
1. Enter your test credentials on the plugin settings and check the "Test Mode" option

2. Create 2-3 test orders. The vouchers can be generated automatically if you have the "Automatically generate vouchers when a new order is made" option or manually by clicking on the "Generate Voucher" button in a test order.

3. Click on "Cancel Voucher" on one of your test orders.

4. Click on "Finalize/Close Order" on the rest of the test orders.

5. Contact Geniki Taxydromiki to inform them that you have completed the necessary actions. They will give you your live/production credentials.

6. Enter the live/production credentials on the plugin's settings page and uncheck the "Test Mode" option.

7. That's it! 

= How do I use the included shortcode? =
Just create a page (for example: example.com/track-and-trace/) and paste the shortcode ( [ifgtapifwoo-track-and-trace] ) in the editor.




== Screenshots ==

== Changelog ==

= Version: 1.0.1 =
Minor Changes

= Version: 1.0.0 =
Initial Release