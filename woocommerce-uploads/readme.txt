=== WooCommerce Uploads ===
Contributors: wpfortune
Tags: woocommerce, upload, files
Requires at least: 3.8
Tested up to: 4.2-alpha
Stable tag: 1.1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let customers upload files easily in your WooCommerce webshop.

== Description ==

The WooCommerce Uploads plugin allows customers to upload one or more files for products a customer has ordered.
Once an order is processed, for each order an upload button will appear on the order overview page under ‘My account’. 
On the order detail page a customer can upload their files easily for their ordered products.


== Installation ==

1. Make sure WooCommerce is installed.
1. Download the .zip file.
1. Upload the entire ‘woocommerce-uploads’ directory into the ‘/wp-content/plugins/’ directory.
1. Activate the plugin through the ‘Plugins’ menu in WordPress.
1. Configure the plugin by going to WooCommerce -> Uploads (see ‘Getting started’ on this page for more information).
1. Enter the license key for this plugin in our WP Fortune plugin to get support and updates.


== Frequently Asked Questions ==

For the WooCommerce Uploads FAQ, please check the Frequently Asked Questions section in our [helpdesk](https://wpfortune.zendesk.com/hc/en-us/articles/201072531-Frequently-Asked-Questions-FAQ-)

== Changelog ==

= 2015.01.29 - 1.1.7 =
* Fixed problem with zip files

= 2015.01.26 - 1.1.6 =
* Added: Download all button for single order in admin (zip)
* Fix: Small bug when no uploads are needed (on variation-level)
* Fix: Bug when creating image / download urls
* Fix: Several notices

= 2014.12.17 - 1.1.5 =
* Added: Hook 'wpf_upload_complete' added when file is successfully uploaded
* Fix: Warning when no products are available for an order

= 2014.11.06 - 1.1.4 =
* Fix: Typo in approve e-mail to customer

= 2014.10.20 - 1.1.3 =
* Added: Support for dynamic variations
* Added: Check all checkbox to approve all order uploads at once
* Fix: When deleting file directly after ajax upload, reset upload limit check

= 2014.10.10 - 1.1.2 =
* Added: Possibility to let customers download their own files

= 2014.10.02 - 1.1.1 =
* Fix: Fixed small bug using the AJAX uploader preventing further uploads when an error occurs

= 2014.10.01 - 1.1.0 =
* Added: Posibility to show upload boxes only for certain product variations
* Added: Filter to customize 'View cart' button when using the Uploads Before Add-On
* Fix: Fixed small bug when using slashes in custom messages on the settings page

= 2014.09.25 - 1.0.9 =
* Fix: Error with upload boxes when using Upload Before Add-on with the HTML uploader
* Tweak: Start upload button is not visible now when Autostart is enabled

= 2014.09.15 - 1.0.8 =
* Fix: Made small changes to updater to work correctly with WP Fortune plugin v1.0.4 and above
* Fix: Disabling styling in settings works correctly now

= 2014.09.12 – 1.0.7 =
* Fix: Fixed small bug on My account page when using WooCommerce 2.2.2

= 2014.09.10 - 1.0.6 =
* Fix: Uploads are now processed correctly when using SSL (https) on admin page

= 2014.09.09 - 1.0.5 =
* Fix: Plugin is now compatible with https version of wpfortune.com

= 2014.09.08 - 1.0.3 =
* Fix: Order numbers are now used in e-mail overview if 'order number' option is enabled
* Added: New option to start uploading automatically when files are added
* Added: Possibility to preserve filename in uploaded filename

= 2014.09.03 - 1.0.2 =
* Fix: Image check function now supports PHP 5.2 and lower
* Fix: PNG thumbnails can have transparent background now
* Fix: Uploads now working correctly when having 'Order number' selected as directory name
* Added: Support for WooCommerce Uploads Restore Settings plugin
* Added: Possibility to enable upload paths outside root directory

= 2014.09.02 - 1.0.1 =
* Fix: You can now choose more than 10 uploads per upload box

= 2014.09.01 - 1.0.0 =
* Stable release of WooCommerce Uploads