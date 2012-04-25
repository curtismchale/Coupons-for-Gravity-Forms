=== Coupons for Gravity Forms ===

Contributors: Curtis McHale
Tags: gravity forms, coupons
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 0.1

Adds coupon support to Gravity Forms.

== Description ==

Adds coupon support to Gravity Forms. Requires Gravity Forms and the
Gravity Forms PayPal addon.

Currently it's quick and dirty. You add coupons by adding to the array in the plugin.
All discounts are percentage based only. Coupon codes are case sensitive. Only tested
with PayPal.

== Installation ==

1. Extract to your wp-content/plugins/
folder.

2. Activate the plugin.

3. Add coupons by adding to the array inside the plugin.

4. Set up the coupon fields in your form with the instructions below.

  You just enter the coupons in the $coupon variable in the code.
  Then in the Gravity Forms admin you add the "gfcoupon" class to
  whatever field will be your coupon field, "gfdiscount" to a new single
  line text field you will need to add, and make sure you have a total
  field on your form.

== Changelog ==

= 0.1 =

- Quick and dirty version after getting a bunch of emails in the same week
