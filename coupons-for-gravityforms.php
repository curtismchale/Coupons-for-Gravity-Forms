<?php
/*
Plugin Name: Coupons for Gravity Forms
Plugin URI:
Description: Adds coupon support for Gravity Forms
Version: 0.1
Author: WP Theme Tutorial - Curtis McHale
Author URI: http://wpthemetutotial.com/about/
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


/* calls the new CPT  that will make up the coupons */
require_once( plugin_dir_path( __FILE__ ) . '/create-coupon-cpt.php' );

/*
 * Produces print_r inside <pre> limited to development users
 *
 * @param string $data The variable we want to print
 * @uses get_the_author_meta
 * @uses current_user_can
 * @ueses in_array
 */
function sfn_gfcoupon_print_r($data) {

  global $current_user;

  if( WP_DEBUG || current_user_can( 'administrator' ) ){
   echo "<pre>";
      print_r($data);
   echo "</pre>";
  }

}

/**
 * Builds and returns the coupons that have been entered by the user.
 *
 * @return array 	The array of coupons for the form
 *
 * @since 	1.0
 * @author 	WP Theme Tutorial, SFNdesign
 *
 * @todo 	it would probably be a good idea to save in a transient and only regenerate when needed
 */
function sfn_gfcoupon_build_coupons(){

	$coupons = array();

	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'sfn_gfcoupon',
		'post_status' => 'scheduled'
	);

	$build_coupons = get_posts( $args );

	// building our array of coupons
	foreach( $build_coupons as $cou ){
		$coupon_name = get_post_meta( $cou->ID, '_sfn_gfcoupon_name', true );
		$coupon_value = get_post_meta( $cou->ID, '_sfn_gfcoupon_value', true );
		$coupons[ $coupon_name ] = $coupon_value;
	}

	return $coupons;
}

add_filter('gform_pre_render', 'sfn_gfcoupon_add_coupon_support');
function sfn_gfcoupon_add_coupon_support($form){

    $coupon_field = sfn_gfcoupon_get_field_by('class', 'gfcoupon', $form['fields']);
    $discount_field = sfn_gfcoupon_get_field_by('class', 'gfdiscount', $form['fields']);
    $total_field = sfn_gfcoupon_get_field_by('type', 'total', $form['fields']);

    if(!$coupon_field || !$discount_field)
        return $form;

    ?>

    <style type="text/css">

        .apply-coupon { text-transform: uppercase; font-size: .75em; padding: .2em .5em; background-color: #f7f7f7; border: 1px solid #e7e7e7;
            text-decoration: none; color: #333; margin-left: .5em; }
        .apply-coupon:hover { background-color: #ffffe0; border-color: #e6db55; }
        .apply-coupon:active { background-color: #f2f2c9; }
        .apply-coupon.success { background-color: #eaf2fa; border-color: #87c0fa; color: #238efa; cursor: default; }
        .gfdiscount { position: absolute; visibility: hidden; }

    </style>

    <script type="text/javascript">

        var gfcoupon = new Object();
        gfcoupon.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

        jQuery(document).ready(function(){

            var couponField = jQuery('#input_<?php echo $coupon_field['formId']; ?>_<?php echo $coupon_field['id']; ?>');
            var discountField = jQuery('#input_<?php echo $discount_field['formId']; ?>_<?php echo $discount_field['id']; ?>');
            var totalField = jQuery('#input_<?php echo $total_field['formId']; ?>_<?php echo $total_field['id']; ?>');

            var formID = '<?php echo $coupon_field['formId']; ?>';
            var couponApplied = false;
            var couponButton = '<a href="" class="apply-coupon">Apply</a>';

            // add the the "apply" button
            jQuery(couponField).after(couponButton);

            // turn off autocomplete for the discount field
            jQuery(discountField).attr('autocomplete', 'off');

            // reset "apply" button, when price field is modified
            jQuery('.gfield_price').change(function(){
                jQuery(discountField).val('');
                couponApplied = false;
                gformCalculateTotalPrice(formID);
                jQuery('a.apply-coupon').after(couponButton).remove();
            });

            // handle "apply" button click
            jQuery('a.apply-coupon').live('click', function(event){
                event.preventDefault();

                if(couponApplied)
                    return;

                var code = jQuery(couponField).val();
                var total = jQuery(totalField).val();
                var value = 0;
                var discount = 0;

                jQuery.post(gfcoupon.ajaxurl, { action : 'validate_coupon', code : code }, function(response){
                    if(response != 0) {
                        value = parseInt(response);
                        discount = (total * value) / 100;
                        discount = discount.toFixed(2);

                        jQuery(discountField).val('-' + discount);
                        jQuery('a.apply-coupon').addClass('success').html('<strong>-' + gformFormatMoney(discount) + '</strong>');

                        var newPrice = ( total - discount );
                        jQuery( totalField ).val( newPrice );
                        jQuery( '.ginput_total_'+formID ).html( '$'+newPrice );

                        couponApplied = true;
                    } else {
                        alert('This coupon is not valid.');
                    }
                });

            });

        });

    </script>

    <?php
    return $form;
}

/**
 * Gets the form by class or type depending on the values passed.
 *
 * @param 	string 	$attr 			req		The attribute you want to find by
 * @param 	string 	$attr_value 	req		The value you're searching for
 * @param 			$fields
 * @return bool
 *
 * @since 	0.1
 * @author 	WP Theme Tutorial, SFNdesign
 */
function sfn_gfcoupon_get_field_by($attr, $attr_value, $fields){

    foreach($fields as $field){

        switch($attr){
        case 'class':

            if($field['cssClass'] == $attr_value)
                return $field;

            break;
        case 'type':

            if($field['type'] == $attr_value)
                return $field;

            break;
        }

    }
    return false;
}

/**
 * Checks to make sure that there is a coupon with the name passed
 *
 * @author    WP Theme Tutorail, SFNdesign
 * @since     0.1
 *
 */
function sfn_gfcoupon_validate_coupon() {

	$coupons = sfn_gfcoupon_build_coupons();

    $code = $_POST['code'];

    if(array_key_exists($code, $coupons)) {
        echo $coupons[$code];
    } else {
        echo 0;
    }

    exit;
}
add_action('wp_ajax_validate_coupon', 'sfn_gfcoupon_validate_coupon');
add_action('wp_ajax_nopriv_validate_coupon', 'sfn_gfcoupon_validate_coupon');

/**
 * Validates the coupon and passes the discount to a global
 * so that we can use it later.
 *
 * @param     $validation_result
 * 
 *
 * @author    WP Theme Tutorial, SFNdesign
 * @since     0.1
 *
 */
function sfn_gfcoupon_coupon_validation($validation_result){
    global $total;

	$coupons = sfn_gfcoupon_build_coupons();

    $form = $validation_result['form'];

    foreach($form['fields'] as &$field){

        if($field['cssClass'] == 'gfdiscount')
            $discount_field = &$field;

        if($field['cssClass'] == 'gfcoupon')
            $coupon_field = &$field;

        if($field['type'] == 'total')
            $total_field = &$field;

    }

    if(empty($discount_field) || empty($coupon_field))
        return $validation_result;

    $discount = RGForms::post("input_{$discount_field['id']}");
    $coupon = RGForms::post("input_{$coupon_field['id']}");
    $total = RGForms::post("input_{$total_field['id']}");

    if(empty($discount))
        return $validation_result;

    if(!array_key_exists($coupon, $coupons)) {

        $coupon_field['failed_validation'] = true;
        $coupon_field['validation_message'] = "This coupon is not valid.";

        $validation_result['is_valid'] = false;
        $validation_result['form'] = $form;

        return $validation_result;
    }

    $coupon_value = $coupons[$coupon];
    $discount_abs = abs($discount);
    $discount_check = round(($total + $discount_abs) * ($coupon_value / 100));
    $discount_abs = round($discount_abs);

    if($discount_abs != $discount_check) {
        $validation_result['is_valid'] = false;
        $coupon_field['failed_validation'] = true;
        $coupon_field['validation_message'] = "There was an error processing this coupon.";

        $validation_result['form'] = $form;
        return $validation_result;
    }

    $validation_result['is_valid'] = true;
    $discount_field['failed_validation'] = false;

    return $validation_result;
}
add_filter( 'gform_validation', 'sfn_gfcoupon_coupon_validation' );

/**
 * Updates the product price with the new total so that it sends properly to PayPal
 *
 * @param $product_info
 * @param $form
 * @param $lead
 * @return mixed
 *
 * @since 	1.0
 * @author	WP Theme Tutorial, SFNdesign
 */
function sfn_gfcoupon_update_product_info( $product_info, $form, $lead ){
	global $total;

	$id = $form['fields'][0]['id'];

	/**
	 * Figuring out which field is the product field so that we can know
	 * what field to override before we send it to PayPal
	 */
	foreach( $form['fields'] as $field ){
		if( $field['type'] == 'product' ){
			$id = $field['id'];
		}
	}

	$product_info['products'][$id]['price'] = $total;

	return $product_info;
}
add_filter( 'gform_product_info', 'sfn_gfcoupon_update_product_info', 10, 3 );
?>
