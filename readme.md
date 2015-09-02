=== UF Store ===
Contributors: James W Buke
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3

A super simple store plugin by Umbrella Fish.

== Description ==

UF Store is a store plugin currently under development. It relies on ACF Pro 5.x and WP Session.

== Installation ==

Upload ACF Pro 5.x and WP Session and activate.

Upload UF Store to your /wp-content/plugins/ directory and activate.


## Settings ##

Settings are under Store > Store Settings.

### Store Info ###

*Store Title* - Select a title for your store. This is used on reciepts and similar areas.

### Store Setup ###

You will need to create 3 or 4 pages and then select them in the dropdowns (Store page is optional. Shortcodes can be used instead to pull specific categories).

### Email Settings ###

For reciepts and confirmation emails

### Shipping Settings ##

USPS is the only available shipping service at this time. Enable which countries you ship to. Assumes you are shipping from the United States.

### Stipe Settings ###

Stripe is the only currently available shipping platform.


== Adding a Product ==

Store > Add New

Give your product a name, description, and a category if applicaple.

The Featured Image is used on the Store Pages

## Settings ##

### Product Information ###

*Base Price* - Cost of your item in pennies, without shipping

### Photos ###

Photos are useful if you do not have variable colors (see Extra Fields).

### Extra Fields ###

*Text* - Fields that allow a user to enter text. Great for custom printed items

*Dropdown* - Allow your users to select from a dropdown list of options (under development)

*Sizes* - Control inventory and multiple sizes

*Colors* - Advanced photo control for different colors, includes multiple sizes and inventory handling

### Shipping ###

*Included* - No additional shipping cost

*Download* - Allows you to send a link via email instead of physically shipping an item (under development)

*Flat Rate* - A single cost for shipping. Options available for US or Internationally

*Group Similar Items* - Enter a cost for up to X amounts of product to ship together. (ex. $10 cost for 5 items would mean 1 item costs $10 shipping, 5 items also cost $10 shipping, 6 items would bump the cost to $20 shipping, etc.)

*United States Postal Service* - Enter Weight in ounces of total package to estimate shipping (Inore shipping type - this is automatically determined based on destination and weight).

*Packaging* - Under development


== Shortcodes ==

Display specific products using Product Categories. Categorize as needed, and display using shortcode slug as follows,

[ufstore category="home-page"]

== Developer Extending ==

All the templates can be extended and overridden to fit your theme and needs. Documentation coming soon.

== Changelog ==