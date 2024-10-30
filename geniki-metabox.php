<?php 



//Action to insert styles
add_action( 'admin_print_styles', 'ifgtapifwoo_metabox_styles' );
function ifgtapifwoo_metabox_styles(){
	$post_id = get_the_ID();
	$isClosed = get_post_meta($post_id, 'geniki-taxidromiki-is-closed', true);

	if ($isClosed){
		$css = 'div#geniki_meta_box{border:1px solid green!important;}';
		$css .= '#geniki_meta_box > div.postbox-header{border-bottom:1px solid green;}';
		wp_add_inline_style( 'woocommerce_admin_styles', $css );
	}
}


add_action( 'add_meta_boxes_shop_order', 'ifgtapifwoo_add_meta_box' );

function ifgtapifwoo_add_meta_box( $post ) {

	$screenAction = sanitize_text_field( $_GET['action'] );

	if($screenAction == "edit"){

		$isClosed = get_post_meta($post->ID, 'geniki-taxidromiki-is-closed', true);
		
		if(!$isClosed) {

			add_meta_box( 'geniki_meta_box', '<i class="fa-solid fa-box"></i>Geniki Taxydromiki', 'ifgtapifwoo_meta_box_callback_function', 'shop_order', 'side', 'core' );
		} else {

			add_meta_box( 'geniki_meta_box', '<i style="color:green;" class="fa-solid fa-circle-check"></i>Geniki Taxydromiki', 'ifgtapifwoo_meta_box_callback_function', 'shop_order', 'side', 'core' );


		}
	}

}

function ifgtapifwoo_meta_box_callback_function() {

	$post_id = get_the_ID();

	$jobID = get_post_meta($post_id, 'geniki-taxidromiki-jobid', true);
	$voucherNum = get_post_meta($post_id, 'geniki-taxidromiki-voucher-number', true);
	$voucherURL = get_post_meta($post_id, 'geniki-taxidromiki-voucher-url', true);
	$isClosed = get_post_meta($post_id, 'geniki-taxidromiki-is-closed', true);
	$isCanceled = get_post_meta($post_id, 'geniki-taxidromiki-is-canceled', true);
	$voucherDate = get_post_meta($post_id, 'geniki-taxidromiki-voucher-date', true);

	$order = wc_get_order( $post_id );
	$shipping_items = $order->get_items( 'shipping' );
	$shipping_method = '';

	if( $shipping_items ){
		$shipping_method = reset($shipping_items)->get_method_id();
	}

	if ($shipping_method != 'local_pickup') {

		echo '<strong><i class="fa-solid fa-calendar"></i> Date: </strong>' . ($voucherDate ? date('F d Y \/ G:i:s', strtotime($voucherDate) ) : 'Voucher not created yet') ;
		echo '<br>';
		echo '<strong><i class="fa-solid fa-circle-info"></i> JobID: </strong>' . ($jobID ? wp_kses_post($jobID) : 'Job not created yet');
		echo '<br>';
		echo '<strong><i class="fa-solid fa-hashtag"></i> Voucher No.: </strong>' . ($voucherNum ? wp_kses_post($voucherNum) : 'Voucher not created yet');

		echo '<br>';
		echo '<strong><i class="fa-solid fa-check"></i> Job Closed: </strong>' . ($isClosed ? 'Yes' : 'No');
		echo '<br>';
		echo '<strong><i class="fa-solid fa-ban"></i> Job Canceled: </strong>' . ($isCanceled ? 'Yes' : 'No');
		echo '<br><br>';

		//Print Voucher Button
		if($voucherURL && !$isCanceled) {

			echo '<a class="button" style="width:100%!important; text-align:center;" target="_blank" href="' . esc_url($voucherURL) . '"><strong><i class="fa-solid fa-print"></i> Print Voucher</strong></a>';
		} else {

			echo '<a class="button disabled" style="width:100%!important; text-align:center;"><strong><i class="fa-solid fa-print"></i> Print Voucher</strong></a>';
		}
		//Print Voucher Button - END

		//Voucher Action form
		echo '<form action="" method="post">';

		echo '<input type="hidden" name="geniki-metabox-nonce" value="' . esc_attr( wp_create_nonce() ) . '">';

		echo '<br><br>';

		

		//Regenerate Voucher Button
		echo '<button ' . ($isClosed ? 'disabled' : '') . ' class="button" type="submit" style="width:100%!important;" name="generate-voucher-submit" value="true"><i class="fa-solid fa-repeat"></i> <strong>' . ($jobID ? 'Re-' : '') . 'Generate Voucher</strong></button>';

		echo '<br><br>';

		if (! $isCanceled) {
			//Cancel Button
			echo '<button ' . ($isClosed || !$jobID ? esc_attr('disabled') : '') . ' class="button" type="submit" style="width:100%!important;" name="cancel-voucher-submit" value="true"><i class="fa-solid fa-xmark"></i> <strong>Cancel Voucher</strong></button>';

		} else {
			//Re-enable Button
			echo '<button class="button" type="submit" style="width:100%!important;" name="cancel-voucher-submit" value="true"><i class="fa-solid fa-rotate-left"></i> <strong>Un-Cancel Voucher</strong></button>';
		}
	
		//Finalize Button and div message
		if( !$isCanceled && !$isClosed && $jobID ) {
			echo '<br><br>';

			echo '<div style="border: 1px solid red; padding: 10px; border-radius: 2px;">';

			echo '<strong>Important!</strong> Make sure the order info is correct before finalizing the voucher. Once you click the "Finalize" button, you won\'t be able to Re-Generate or Cancel the current voucher. ';

			echo '<br><br>';

			echo '<button ' . ( $isClosed ? esc_attr('disabled') : '') . ' class="button" type="submit" style="width:100%!important;" name="close-voucher-submit" value="true"><i class="fa-solid fa-check"></i> <strong>Finalize/Close Voucher</strong></button>';

			echo '</div>';

		} else if ( $isClosed ) {

			echo '<br><br>';
			echo '<div style="color: white; background-color: #00a32a; padding: 10px; border-radius: 2px;">';
			echo '<strong><i class="fa-solid fa-circle-check"></i> Voucher has been finalized and will be picked up by Geniki Taxydromiki.</strong>';
			echo '</div>';
		}
		

		echo '</form>';

	} else {
		echo 'Unavailable because shipping method is Local Pickup';
	}
	
	

}

add_action( 'save_post', 'ifgtapifwoo_metabox_button_do' );
function ifgtapifwoo_metabox_button_do($post_id) {

    // Check if our nonce is set (and our cutom field)
    if ( ! isset( $_POST[ 'geniki-metabox-nonce' ] ) )
        return $post_id;

	$nonce = $_POST[ 'geniki-metabox-nonce' ];

	// Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce ) )
        return $post_id;

    // Checking that is not an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

	
	// Action to make or (saving data)
	if ( isset($_POST['close-voucher-submit']) ) {

		ifgtapifwoo_closePendingJob( $post_id );

	} else if ( isset($_POST['cancel-voucher-submit']) ){

		ifgtapifwoo_cancelJob( $post_id );

	} else if ( isset($_POST['generate-voucher-submit']) ){

		// $isCanceled = get_post_meta($post_id, 'geniki-taxidromiki-is-canceled', true);

		if (!metadata_exists('post', $post_id, 'geniki-taxidromiki-is-canceled')){

			ifgtapifwoo_generateVoucher( $post_id );

		} else {
			
			ifgtapifwoo_cancelJob( $post_id );
			ifgtapifwoo_generateVoucher( $post_id );
		}
		
	}

}




?>