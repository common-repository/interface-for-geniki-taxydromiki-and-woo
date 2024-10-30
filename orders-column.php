<?php

//Add voucher column with button to Voucher PDF
function ifgtapifwoo_new_order_column( $columns ) {

    $columns['geniki-taxidromiki-voucher'] = 'Voucher';
    return $columns;
  }
add_filter( 'manage_edit-shop_order_columns', 'ifgtapifwoo_new_order_column' );

function ifgtapifwoo_helper_get_order_meta( $order, $key = '', $single = true, $context = 'edit' ){

		$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
		$value    = get_post_meta( $order_id, $key, $single );
		return $value;
}

function ifgtapifwoo_voucher_column_css() {

    $css = '.widefat .column-geniki-taxidromiki-voucher { width: 3%!important; text-align: center;}';
    $css .= '.voucher-button{ text-align: center; font-size: 15px!important;}';
    $css .= '.voucher-button>.dashicons {vertical-align: middle;}';
    wp_add_inline_style( 'woocommerce_admin_styles', $css );
}
add_action( 'admin_print_styles', 'ifgtapifwoo_voucher_column_css' );

function ifgtapifwoo_voucher_column_content($column){
	
		global $post;

		if ($column === 'geniki-taxidromiki-voucher'){
			
			$order = wc_get_order( $post->ID );
			$url = ifgtapifwoo_helper_get_order_meta($order, 'geniki-taxidromiki-voucher-url');
			$isCanceled = ifgtapifwoo_helper_get_order_meta($order, 'geniki-taxidromiki-is-canceled');
			$isClosed = ifgtapifwoo_helper_get_order_meta($order, 'geniki-taxidromiki-is-closed');
			$voucherNumber = ifgtapifwoo_helper_get_order_meta($order, 'geniki-taxidromiki-voucher-number');

			if ($url && !$isCanceled && !$isClosed) {
				echo '<a class="button voucher-button" target="_blank" href="' . esc_url($url) . '"><i class="fa-solid fa-print"></i><a>';

				echo '<br>' . wc_help_tip('Voucher #: '.$voucherNumber);

			} else if ( $url && !$isCanceled && $isClosed) {
				echo '<a class="button voucher-button" style="color:green;border-color:green;" target="_blank" href="' . esc_url($url) . '"><i class="fa-solid fa-print"></i><a>';

				echo '<br>' . wc_help_tip('Voucher #: '.$voucherNumber);

			} else {
				echo '<a class="button voucher-button disabled" target="_blank"><i class="fa-solid fa-print"></i><a>';
			}

			
			
		}
		
}
add_action( 'manage_shop_order_posts_custom_column', 'ifgtapifwoo_voucher_column_content' );
//Add voucher column with button to Voucher PDF - END

?>