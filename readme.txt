=== Coupons for Gravity Forms ===

Contributors: Curtis McHale
Tags: gravity forms, coupons
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 1.2

~Current Version:1.3~

Adds coupon support to Gravity Forms.

== Description ==

Adds coupon support to Gravity Forms. Requires Gravity Forms and the Gravity Forms PayPal addon. Coupons expire and can be % or $ based discounts.

Add Coupons from the coupon menu under Settings. To have a coupon live make it a 'scheduled' WordPress post. When a coupon is 'published' it will no longer work.

There is currently NO support for coupons/discounts on subscriptions.

== Installation ==

1. Extract to your wp-content/plugins/ folder.

2. Activate the plugin.

3. Add coupons by adding items to the coupon custom post type. You can
find the CPT under Settings/Coupons.

4. Set up the coupon fields in your form with the instructions below or
use the included Gravity Forms form export as a basis for your form.

== Usage ==

=== Form Setup ===

1. Create a new form by clicking Forms/New Form

2. Name the form as appropriate for your usage

3. Add a 'Product' field titling as suits for your project
  - disable the quantity option on the field
  - field type should be set to single product
  - add a price

4. Add a 'Single Line Text' field. This is where the user will add the
  coupon code
  - title the field 'Coupon Code' or whatever is appropriate for your
    usage.
  - click the advanced tab and add the CSS Class of 'gfcoupon' to the
    field

5. Add another single line text field
  - label it 'discount - hidden'
  - click the advanced tab and add the CSS Class of 'gfdiscount' to the
    field

6. Add a total field

7. Add whatever other fields are required. The PayPal payment plugins
requires Name (first, last), addresss and email.

8. Save the form and set up the PayPal payment options.

=== Adding Coupons ===

1. Go to Settings/Coupons

2. Click 'Add New' to add a new coupon.

3. Add a title to the new coupon so you can easily see what coupon you
are viewing in the WordPress admin interface.

4. Choose the text the user will need to type in to make the coupon
work. This is case sensitive.

5. Choose the value of the coupon discount.

6. Choose if the coupon should be a % or $ based discount.

7. On the right side of the WordPress admin, schedule the post for a
date in the future. This date is the last date that the coupon will
work. Coupons need to be SCHEDULED to work.

8. Click the schedule button to schedule the coupon as active.


== Changelog ==

= 1.3 =

- documentation update
- added sample form for download

= 1.1 =

- adding the Github WordPress plugin updater class https://github.com/jkudish/WordPress-GitHub-Plugin-Updater

= 1.0 =

- added coupon custom post type
- added % and $ based discounts
- updated for latest GF and PayPal plugins
- added coupon expiration
- Many thanks to http://www.onlinecardclasses.com/stretchyourstamps/ for needing the features and financing the development

= 0.1 =

- Quick and dirty version after getting a bunch of emails in the same week
