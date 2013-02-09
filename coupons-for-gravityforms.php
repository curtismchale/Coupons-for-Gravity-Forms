<?php
/*
Plugin Name: Coupons for Gravity Forms
Plugin URI:
Description: Adds coupon support for Gravity Forms
Version: 1.3
Author: WP Theme Tutorial - Curtis McHale
Author URI: http://wpthemetutotial.com/about/
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * @TODO - bug - get the pricing to not change during Ajax form submit
 * @todo update the 'expire/published' notice in the admin so that the language reads better
 */

/* calls the new CPT  that will make up the coupons */
require_once( plugin_dir_path( __FILE__ ) . '/create-coupon-cpt.php' );

/**
 * Updater class for WordPress plugins hosted with Github.
 *
 * @since 	1.1
 * @author 	WP Theme Tutorial, Curtis McHale
 * @link 	https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
 */
function sfn_gfcoupon_github_updater(){

  /* includes the update from Github code */
  require_once( plugin_dir_path( __FILE__ ) . '/updater.php' );

  define('WP_GITHUB_FORCE_UPDATE', true);

  if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
      $config = array(
          'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
          'proper_folder_name' => 'coupons-for-gravityforms', // this is the name of the folder your plugin lives in
          'api_url' => 'https://api.github.com/repos/curtismchale/Coupons-for-Gravity-Forms', // the github API url of your github repo
          'raw_url' => 'https://raw.github.com/curtismchale/Coupons-for-Gravity-Forms/master', // the github raw url of your github repo
          'github_url' => 'https://github.com/curtismchale/Coupons-for-Gravity-Forms', // the github url of your github repo
          'zip_url' => 'https://github.com/curtismchale/Coupons-for-Gravity-Forms/zipball/master', // the zip url of the github repo
          'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
          'requires' => '3.0', // which version of WordPress does your plugin require?
          'tested' => '3.4', // which version of WordPress is your plugin tested up to?
          'readme' => 'readme.txt' // which file to use as the readme for the version number
      );
      new WPGitHubUpdater($config);
  }
}
add_action( 'init', 'sfn_gfcoupon_github_updater' );

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
		'post_status' => 'future'
	);

	$build_coupons = get_posts( $args );

	// building our array of coupons
  foreach( $build_coupons as $cou ){
		$coupon_name = get_post_meta( $cou->ID, '_sfn_gfcoupon_name', true );
    $coupon_value = get_post_meta( $cou->ID, '_sfn_gfcoupon_value', true );
    $coupon_type = get_post_meta( $cou->ID, '_sfn_gfcoupon_type', true );
    $id = $cou->ID;

    $coupons[] = 	array(
      'name' => $coupon_name,
      'value' => $coupon_value,
      'type' => $coupon_type,
    );
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

        .apply-coupon {text-transform:uppercase; font-size:.75em; font-weight:bold; padding:.2em .5em; background-color:#CCC; border: 1px solid #333;
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

                jQuery.post(gfcoupon.ajaxurl, { action : 'validate_coupon', dataType: 'json', data : code }, function(response){

					// parsing our request in to variables so we can use it
					var return_value = JSON.parse( response );
					var coupon_type = return_value.type;
					var coupon_value = return_value.value;

                    if(response != 0) {
                        value = parseInt( coupon_value );

						// handle the % based coupon math
						if( coupon_type === 'percent' ){
							discount = (total * value) / 100;
						}

						// handle the $ based coupon math
						if( coupon_type === 'dollar' ){
							discount = value;
						}
                        discount = discount.toFixed(2);

                        jQuery(discountField).val('-' + discount);
                        jQuery('a.apply-coupon').addClass('success').html('<strong>-' + gformFormatMoney(discount) + '</strong>');

                        var newPrice = ( total - discount );
                        jQuery( totalField ).val( newPrice );
                        var currency = new Currency( gf_global.gf_currency_config );
                        jQuery( '.ginput_total_'+formID ).html( currency.toMoney( newPrice ) );

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
 * @author    WP Theme Tutorial, SFNdesign
 * @since     0.1
 *
 */
function sfn_gfcoupon_validate_coupon() {

	$coupons = sfn_gfcoupon_build_coupons();

  	$code = $_POST['data'];

	foreach( $coupons as $coupon ){
		if( $code === $coupon['name'] ){
		  $cou = json_encode( $coupon );
		  echo $cou;
		}
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

	foreach( $coupons as $cou ){
		if( $cou['name'] === $coupon ){
			$coupon_name = $cou['name'];
			$coupon_value = $cou['value'];
			$coupon_type = $cou['type'];
			break;
		}
	}

	// coupon name is empty so we don't have a valid coupon
    if( empty( $coupon_name ) ) {

        $coupon_field['failed_validation'] = true;
        $coupon_field['validation_message'] = "This coupon is not valid.";

        $validation_result['is_valid'] = false;
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
