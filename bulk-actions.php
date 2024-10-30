<?php 


// Adding to admin order list bulk dropdown a custom action 'custom_downloads'
add_filter( 'bulk_actions-edit-shop_order', 'ifgtapifwoo_register_bulk_actions', 20, 1 );

function ifgtapifwoo_register_bulk_actions( $actions ) {

    $actions['ifgtapifwoo_generate_vouchers'] = __( 'Gen.Taxydromiki - Generate Vouchers', 'woocommerce' );

    $actions['ifgtapifwoo_print_vouchers_a4'] = __( 'Gen.Taxydromiki - Print Vouchers (A4)', 'woocommerce' );

    $actions['ifgtapifwoo_print_vouchers_sticker'] = __( 'Gen.Taxydromiki - Print Vouchers (Sticker)', 'woocommerce' );

    return $actions;
}

// Make the action from selected orders
add_filter( 'handle_bulk_actions-edit-shop_order', 'ifgtapifwoo_handle_bulk_actions', 10, 3 );

function ifgtapifwoo_handle_bulk_actions( $redirect_to, $action, $post_ids ) {

    if ( $action == 'ifgtapifwoo_generate_vouchers' ){

    	foreach ( $post_ids as $post_id ) {

    		$isCanceled = get_post_meta($post_id, 'geniki-taxidromiki-is-canceled', true);

			if ($isCanceled){

				ifgtapifwoo_generateVoucher( $post_id );

			} else {
				
				ifgtapifwoo_cancelJob( $post_id );
				ifgtapifwoo_generateVoucher( $post_id );
			}
    	}

    	return $redirect_to = add_query_arg(array(
    			'voucher-generation-success' => '1'
    		), $redirect_to);
        
    } else if( $action == 'ifgtapifwoo_print_vouchers_a4' ) {

    	$vouchersLink = ifgtapifwoo_getVouchersPDF($post_ids, 'Flyer');

    	if($vouchersLink){
    		wp_redirect( $vouchersLink );
        	exit;
    	}
    		

    } else if( $action == 'ifgtapifwoo_print_vouchers_sticker' ) {

    	$vouchersLink = ifgtapifwoo_getVouchersPDF($post_ids, 'Sticker');

    	if($vouchersLink){
    		wp_redirect( $vouchersLink );
        	exit;
    	}

    } else {

    	return $redirect_to; // Exit
    }
    
}



?>
