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
			'public'                => true,
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

/**
 * Adding the hook that adds our metaboxes. Yes that is just a bit meta
 *
 * @since 	1.0
 * @author 	WP Theme Tutorial, SFNdesign
 */
function sfn_gfcoupon_metaboxes_setup(){
	add_action( 'add_meta_boxes', 'sfn_gfcoupon_add_post_meta_boxes' );

	// saving our metaboxes
	add_action( 'save_post', 'sfn_gfcoupon_save_coupon_metabox', 10, 2 );
}
add_action( 'load-post.php', 'sfn_gfcoupon_metaboxes_setup' );
add_action( 'load-post-new.php', 'sfn_gfcoupon_metaboxes_setup' );

/**
 * Adding the metabox function to the proper CPT and in the proper spot on the site
 *
 * @since 	1.0
 * @author	WP Theme Tutorial, SFNdesign
 */
function sfn_gfcoupon_add_post_meta_boxes(){

	add_meta_box(
		'gfcoupon',
		'Coupon',
		'sfn_gfcoupon_meta_box',
		'sfn_gfcoupon',
		'normal',
		'high'
	);
}

function sfn_gfcoupon_meta_box( $object, $box ){

	wp_nonce_field( 'sfn_gfcoupon_save_coupon', 'sfn_gfcoupon_nonce' );
?>
	<p>
		<label for="sfn_gfcoupon_name"><?php _e( 'Coupon Name, the value the user will type.' ); ?></label>
		<br />
		<input class="left" type="text" name="sfn_gfcoupon_name" id="sfn_gfcoupon_name" value="<?php echo esc_attr( get_post_meta( $object->ID, '_sfn_gfcoupon_name', true ) ); ?>" size="10" />
	</p>

	<p>
		<label for="sfn_gfcoupon_value"><?php _e( 'Coupon Value, how much off?' ); ?></label>
		<br />
		<input class="left" type="text" name="sfn_gfcoupon_value" id="sfn_gfcoupon_value" value="<?php echo esc_attr( get_post_meta( $object->ID, '_sfn_gfcoupon_value', true ) ); ?>" size="10" />
	</p>
<?php
}

/**
 * Saves the Quote meta boxes
 *
 * @since 1.0
 *
 * @param 	integer $post_id 	required 	The id of the post
 * @param 	object 	$post 		required	The whole post object
 *
 * @return bool/void
 */
function sfn_gfcoupon_save_coupon_metabox( $post_id, $post ){

	// check nonce before proceeding
	if ( ! isset( $_POST[ 'sfn_gfcoupon_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'sfn_gfcoupon_nonce' ], 'sfn_gfcoupon_save_coupon' ) ) return $post_id;

	// check the user
	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

	// check sanitize and save the data
	$coupon_name	= empty( $_POST['sfn_gfcoupon_name'] ) ? delete_post_meta( $post_id, '_sfn_gfcoupon_name' ) : update_post_meta( $post_id, '_sfn_gfcoupon_name', sanitize_text_field( $_POST[ 'sfn_gfcoupon_name' ] ) );
	$coupon_value 	= empty( $_POST['sfn_gfcoupon_value'] ) ? delete_post_meta( $post_id, '_sfn_gfcoupon_value' ) : update_post_meta( $post_id, '_sfn_gfcoupon_value', sanitize_text_field( $_POST[ 'sfn_gfcoupon_value' ] ) );

}

// @todo add the ability to change from % to $ value coupons
?>