<?php

function ifgtapifwoo_calculate_order_weight( $order ) {

    $weight = 0;
 
	//Calculate order weight
    foreach ($order->get_items() as $item_id => $product_item)
    {
    	$quantity = $product_item->get_quantity(); // get quantity
        $product = $product_item->get_product(); // get the WC_Product object
        $product_weight = $product->get_weight(); // get the product weight
        // Add the line item weight to the total weight calculation
        $weight += floatval($product_weight * $quantity);
	}

	return $weight;
}

function ifgtapifwoo_add_meta( $order_id, $metaKey, $metaValue ) {

	//Add voucher URL to post meta
    if (metadata_exists('post', $order_id, $metaKey)) {

    	$previousValue = get_post_meta($order_id, $metaKey, true);

        update_post_meta( $order_id, $metaKey, sanitize_meta($metaKey, $metaValue, 'post'), $previousValue );

        } else {

        	add_post_meta( $order_id, $metaKey, sanitize_meta($metaKey, $metaValue, 'post') );

        }

}

function ifgtapifwoo_make_admin_post_button ( $args ) {


    $action = $args['action'];
    $callback_function = $args['callback_function'];

    $button_text = $args['button_text'];
    // $button_type = $args['button_type'];

    echo '<form action="admin-post.php" method="POST">';

    echo "<input type='hidden' name='action' value='$action'>";

    wp_nonce_field( $action, $action . '_nonce');

    // echo '<input class="button button-secondary" type="submit" value="' . $button_text . '">';
    submit_button($button_text, 'secondary', 'submit', false);

    echo '</form>';

    add_action( "admin_post_$action", $callback_function );
}



?>