=== Plugin Name ===
Contributors: cxThemes
Tags: woocommerce, cart, email, save, send, share, create, backup, order, url, social, manual, offline, promotion, ecommerce
Requires at least: 3.0.1
Tested up to: 3.6
Stable tag: 2.06
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Empower anyone using your store to Save & Share their Cart.

== Description ==

Save & Share Cart enables anyone to save their carts - simple products, variation products, add-on products, any kind of product - and get a unique link to retrieve the cart later. Click on the link in a chat, social-media, email, bookmark or anywhere and retrieve the cart at any time. There's also a button to send the cart in a styled email showing the products, product pictures, quantities, totals and a big button to click and retrieve the content of that cart. Save & Share Cart empowers shop managers and improves the shopping experience of all your customers - here's some examples:

- Customers Save a cart to use it later
Customers can fill their cart in the morning, then without losing that work they can save the cart to retrieve it later, the next day, or whenever they like.

- Customer send a cart to someone else to pay
Customers can be the one to create a cart then send it to someone else who can retrieve it and checkout and pay for them.

- Pre-fill a cart to assist your customers
Shop Managers can help their a customer via phone, chat, social-media or email by pre-filling a cart then sending it to them so they can fill their cart with one click.

- Bundle products for a promotion
Shop Managers can pre-fill a cart, save it - giving it a memorable name - then use the unique link in a promotion on their site, in a email campaign, on social media, in an ad banners, or anywhere.

Happy Shopping!


== Documentation ==

Please see the included PDF for full instructions on how to use this plugin.


== Changelog ==

2.06
* Load js front-end scripts in the footer, not the header - prevent load blocking.
* Add 'Redirect To' select when saving a cart (available to admins only). Now you can choose where the customer is redirected after retrieving their cart. Also redirect to a custom your own custom redirect URL using `...&cxecrt-redirect=http://customredirecturl.com`.

2.05
* We've stopped coupons sending in all carts - we're looking for a more elegant way to allow admins to add coupons to their carts in future.
* Fixed a possible output of malformed content around the cart totals (for themes that customize the cart template).
* Fixed issue where Total was showing 0,00 in the Email.

2.04
* Updated language translation files.
* Enhanced the main modal-popup animations and functionality.

2.03
* Fixed issue were the settings were not being saved when the `Save changes` button was clicked.

2.02
* Fixed bug where some of our icons would interfere or overwrite other themes icons.

2.01
* Fixed js error preventing Edit Cart button working on the Edit Cart page.

2.00
* Name changed from Email Cart to Save & Share Cart - this major re-write enables us to provide more reliable handling of complicated products like add-ons, variations, etc. Plus includes updates to the interface to make it more user friendly and redesign of the cart email sent to customers. You can find new Help Docs inside the plugin folder and look out for the new Save & Share Cart link here WooCommerce > Save & Share Cart and Settings > Save & Share Cart.

1.18
* Added a safety redirect on front-end cart sending to ensure only one cart is sent.

1.17
* Fixed so is_woocommerce_active() check also works for multisite installations.

1.16
* Refactor the plugin class so plugin is initialized as early as possible. Please let us know if any problems.
* Updated the send email function so it uses the new way of sending WC styled emails.
* Added wpautop and wptexturize to the email content so that line breaks become nicer looking paragraphs and html is encoded.
* Change how we check WooCommerce version number.
* More specific CSS to avoid conflicts.

1.15
* Added Internationalization how-to to the the docs.
* Updated the language files.
* Changes to the order and priority of the loaded language files. Will not effect anyone who is already using internationalization.
* Changed where in the code the WooCommerce and version number checking is done.
* Made more strings translatable.
* Escaped all add_query_args and remove_query_args for security.
* Updated PluginUpdateChecker class.
* Fixed possible non static notice on calling woocommerce_active_check().

1.14
* Changed our WooCommerce version support - you can read all about it here https://helpcx.zendesk.com/hc/en-us/articles/202241041/
* Refactor sending of emails on backend to use ajax for backwards compatibility and not rely on frontend scripts.
* Fixed frontend send message in 2.3.
* Fixed various deprecated function notices.

1.13
* Fixed variations added to other products landing on cart

1.12
* Refactor the way carts are encoded and decoded, to deal with the chnages to the cart in WC2.2
* Fixed bugs that cause incorrect totals, tax totals and other calcualtions after chnages to the cart in WC2.2
* Added link to Email Cart Settings (Settings>Email Cart), also available by clicking the cog icon in the top right of Email Cart page

1.11
* Added styling to email product list table
* Fixed formatted price and added correct tax rate name
* Fixed backwards compatibility on nonce_field
* Fixed update default settings on activate plugin cuasing empty message body
* Fixed tax not showing in email - WC2.0 and below
* Fixed deprecated function issue
* Fixed post variable not set notices
* Fixed settings page compatibility - WC 2.0 and below

1.10
* Added default email template setting so you can customize the default message on the backend and frontend carts
* Added default From address setting that defaults to WooCommerce from address or can be overidden
* Added Tax and Totals to email
* Added sending information to the Send a Copy email so admin can see what the customer or store manager sent
* Moved settings to its own settings page
* Fixed delete line on backend add to cart

1.09
* Added optional CC and BCC fields to Back and Front end forms that can be turned on/off in the General Settings Tab
* Added Send a Copy field to General Settings Tab for permanent BCC so admins can keep track of User actiity

1.08
* Fixed compatibility issue with WC2.1
* Fixed double attribute_ being added to the cart URL
* Fixed formatting issue with the front-end cart
* Changed the front-end call to action to be .button

1.07
* Added filter to add CC or BCC to email
* Updated UpdateChecker class
* Various small bug fixes

1.06
* Added ability to send Variable Products set to "Any" attribute from Email Cart on the front-end Cart Page. Previously these products would be omitted (www.your-site.com/cart/#email-cart)
* Added en_US.mo and en_US.mo files to use for language conversion
* Updated language support for previously disabled text areas

1.05
* Added ability to deep link to Email Cart using www.yoursite.com/cart/#email-cart - allows users to create their own custom buttons linking to Email Cart

1.04
* Updated the Email Cart back-end with a great new looking UI
* 1.41 Check all missing text is language translatable

1.03
* Added ability to Send Cart from the front end WooCommerce Cart
* Added WordPress multilingual support (we invite you to send us your language files. please. thanks)

1.02
* Added ability to add complex variable products to the cart
* Added variable product attributes to the cart in the email to improve the information sent to the user
* Added count to a product item in the query string in order to reduce length. Compatible with older query
* Fixed minor PHP notices

1.01
* Changed form layout for better UI
* Added a drop-down to the form which allows the selection of either the Cart or Checkout page as the landing page
* Added an update-able Share link to the Form
* Fixed Bug: Link now clears cart before adding products to cart and displaying cart/checkout page

1.00
* Initial release
