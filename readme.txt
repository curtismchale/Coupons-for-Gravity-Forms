=== Coupons for Gravity Forms ===

Contributors: Curtis McHale
Tags: gravity forms, coupons
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 1.0

Adds coupon support to Gravity Forms.

== Description ==

Adds coupon support to Gravity Forms. Requires Gravity Forms and the Gravity Forms PayPal addon. Coupons expire and
can be % or $ based discounts.

Add Coupons from the coupon menu under Settings. To have a coupon live make it a 'scheduled' WordPress post. When a
coupon is 'published' it will no longer work.

There is currently NO support for coupons/discounts on subscriptions.

== Installation ==

1. Extract to your wp-content/plugins/ folder.

2. Activate the plugin.

3. Add coupons by adding items to the coupon custom post type.

4. Set up the coupon fields in your form with the instructions below.

  You just enter the coupons in the $coupon variable in the code.
  Then in the Gravity Forms admin you add the "gfcoupon" class to
  whatever field will be your coupon field, "gfdiscount" to a new single
  line text field you will need to add, and make sure you have a total
  field on your form.

== Changelog ==

= 1.0 =

- added coupon custom post type
- added % and $ based discounts
- updated for latest GF and PayPal plugins
- added coupon expiration
- Many thanks to http://www.onlinecardclasses.com/stretchyourstamps/ for needing the features and financing the development

= 0.1 =

- Quick and dirty version after getting a bunch of emails in the same week
