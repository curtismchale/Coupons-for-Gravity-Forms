<?php
/**
 * Contains all of the stuff that build the admin ui for the Coupon CPT
 * including the metaboxes and options for value and %/$ based discounting.
 */

/**
 * Builds the Coupon CPT
 *
 * @uses 	register_post_type
 *
 * @since 	1.0
 * @author 	WP Theme Tutorial, SFNdesign
 */
function sfn_gfcoupon_coupon_cpt(){

	register_post_type('sfn_gfcoupon',
		array(
			'labels'                => array(
				'name'                  => __('Coupons'),
				'singular_name'         => __('Coupon'),
				'add_new'               => __('Add New'),
				'add_new_item'          => __('Add New Coupon'),
				'edit'                  => __('Edit'),
				'edit_item'             => __('Edit Coupon'),
				'new_item'              => __('New Coupon'),
				'view'                  => __('View Coupons'),
				'view_item'             => __('View Coupon'),
				'search_items'          => __('Search Coupons'),
				'not_found'             => __('No Coupons Found'),
				'not_found_in_trash'    => __('No Coupons found in Trash')
				// only useful if like pages 'parent'                => __()
				), // end array for labels
			'description'           => __('Coupons for Gravity Forms'),
			'public'                => false,
			'show_ui'				=> true,
			'show_in_menu'			=> 'options-general.php',
			'menu_position'         => 5, // sets admin menu position
			'hierarchical'          => true, // functions like pages
			'supports'              => array('title', 'revisions'),
			'can_export'            => true,
		)
	);

}

add_action( 'init', 'sfn_gfcoupon_coupon_cpt' );
?>