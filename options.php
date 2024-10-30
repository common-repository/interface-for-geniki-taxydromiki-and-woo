<?php

function ifgtapifwoo_admin_menu()
{

    /* add new top level */
    add_menu_page(__('Gen. Taxidromiki', 'geniki-woocommerce') , __('Gen. Taxidromiki', 'geniki-woocommerce') , 'edit_posts', 'geniki_admin_menu', 'ifgtapifwoo_admin_page', plugins_url('/', __FILE__) . '/images/gt-icon.png');
}

function ifgtapifwoo_admin_page()
{

    global $woocommerce;

    //Handle Errors with GET
    if (isset($_GET['error'])){

        switch ($_GET['error']) {
            case 'dateafter':
                echo '<div class="notice notice-error is-dismissible"><p>Date_From can\'t be after Date_To. Please select a valid date range.</p></div>';
                break;

            case 'dateEmpty':
                echo '<div class="notice notice-error is-dismissible"><p>Date Fields can\'t be empty. Please select a valid date range.</p></div>';
                break;

            case 'auth':
                echo '<div class="notice notice-error is-dismissible"><p>Authentication Error. Make sure your credentials are correct.</p></div>';
                break;
        }

    }
    //Handle Errors with GET - END

    echo '<div class="wrap">';

    echo '<h1>' . __('Settings page for Geniki Taxydromiki', 'geniki-woocommerce') . '</h1>';
    echo '<hr style="border-top:1px solid black;">';
    echo '<p>' . __('In the settings below, enter the required information.<br> Click on "Test Mode" for testing purposes.<br> You can also choose if the voucher should be generated as a sticker or as an A4 page (Flyer).', 'geniki-woocommerce') . '</p>';

    echo '<form method="post" action="options.php">';

    settings_fields('geniki-group');
    do_settings_sections('geniki-group');

    echo '<table class="form-table">';

    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Username', 'geniki-woocommerce') . '</th>';
    echo '<td><input type="text" name="username" value="' . esc_attr( get_option('username') ) . '" /></td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Password', 'geniki-woocommerce') . '</th>';
    echo '<td><input type="text" name="password" value="' . esc_attr( get_option('password') ) . '" /></td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row">' . __('AppKey', 'geniki-woocommerce') . '</th>';
    echo '<td><input type="text" name="appkey" value="' . esc_attr( get_option('appkey') ) . '" /></td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Test Mode', 'geniki-woocommerce') . '</th>';

    $checkedTest = ((int)get_option('testmode') == 1 ? 'checked="checked"' : '');
    
    echo '<td><input type="checkbox" name="testmode"  value="1"  ' . esc_attr($checkedTest) . '/></td>';
    echo '</tr>';

    //Auto Generate voucher on new orders
    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Auto-generate vouchers when a new order is made', 'geniki-woocommerce') . '</th>';

    $checkedAuto = ((int)get_option('autogenerate') == 1 ? 'checked="checked"' : '');

    echo '<td><input type="checkbox" name="autogenerate"  value="1"  ' . esc_attr($checkedAuto) . '/></td>';
    echo '</tr>';
    /**/
    

    //New option - Flyer or Sticker?
    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Voucher Format (Flyer/Sticker)', 'geniki-woocommerce') . '</th>';
    echo '<td>';
    echo '<select id="pagetype" name="pagetype">';

    $pagetype_option = get_option('pagetype');

    echo '<option value="Flyer"' . (($pagetype_option == 'Flyer') ? 'selected="selected"' : '') . '>Flyer</option>';
    echo '<option value="Sticker"' . (($pagetype_option == 'Sticker') ? 'selected="selected"' : '') . '>Sticker</option>';
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '</table>';
    submit_button();
    echo '</form>';
    

    echo '<hr style="border-top:1px solid black;">';

    //Download all vouchers between specified dates
    echo '<h2>Download all vouchers between specified (order) dates</h2>';
    echo '<p>Download all generated vouchers in printable format (PDF) for the all orders between the specified dates. <br>The Flyer option creates a PDF the pages of which are in A4 paper format and includes 3 vouchers per page.<br>The Stickers option creates a PDF the pages of which are in Sticker paper format and includes 1 voucher per page. With this option you can choose to print 3 vouchers per page in the Printing dialog window of your chosen PDF viewer.</p>';

    echo '<form action="admin-post.php" method="POST">';

    echo '<input type="hidden" name="action" value="download_vouchers_pdf">';
    
    wp_nonce_field('download_vouchers_pdf', 'download_vouchers_pdf_nonce');

    echo '<input type="date" id="from" name="vouchers-from">';
   
    echo '<input type="date" id="to" name="vouchers-to" value="' . date('Y-m-d') . '" >';

    echo '<select name="pagetype_pdf" id="pagetype_pdf">';
    echo '<option value="Flyer">Flyer (A4 / 3 per page)</option>';
    echo '<option value="Sticker">Stickers (One per page)</option>';
    echo '</select>';

    echo '<input class="button button-secondary" type="submit" value="Download Vouchers">';

    echo '</form>';

   
    echo '<br><hr style="border-top:1px solid black;">';
    echo '<h2>Shortcode for Track and Trace</h2>';
    echo '<p>This Plugin comes with a shortcode to display an order\'s checkpoints.<br>Just paste the shortcode into any page.</p>';
    echo '<code style="font-weight:bold;">[ifgtapifwoo-track-and-trace]</code>';

    echo '</div>';

}

add_action('admin_menu', 'ifgtapifwoo_admin_menu');
add_action('admin_init', 'ifgtapifwoo_register_settings');

function ifgtapifwoo_register_settings()
{ // whitelist options
    register_setting('geniki-group', 'username');
    register_setting('geniki-group', 'password');
    register_setting('geniki-group', 'appkey');
    register_setting('geniki-group', 'testmode');
    register_setting('geniki-group', 'pagetype');
    register_setting('geniki-group', 'autogenerate');
}


add_action( 'admin_post_download_vouchers_pdf', 'ifgtapifwoo_getVouchersbyDate' );
function ifgtapifwoo_getVouchersbyDate (){


    // Verify that the nonce is valid.
    if ( !isset($_POST[ 'download_vouchers_pdf_nonce' ]) || !wp_verify_nonce( $_POST[ 'download_vouchers_pdf_nonce' ], 'download_vouchers_pdf' ) ) {

        wp_redirect( admin_url('admin.php?page=geniki_admin_menu') );
        exit;
    }

    if( !( $_POST['vouchers-from'] && $_POST['vouchers-to'] ) ){

        wp_redirect( admin_url('admin.php?page=geniki_admin_menu&error=dateEmpty') );
        exit;
    }

    $dateFrom = strtotime($_POST['vouchers-from']);
    $dateTo = strtotime($_POST['vouchers-to']);
    $pagetypePDF = sanitize_text_field($_POST['pagetype_pdf']);

    if ($dateFrom > $dateTo){
        wp_redirect( admin_url('admin.php?page=geniki_admin_menu&error=dateafter') );
        exit;
    }

    $order_ids = wc_get_orders(array(
        'limit'=>-1,
        'type'=> 'shop_order',
        'status'=> array( 'wc-completed' ),
        'date_created'=> $dateFrom .'...'. $dateTo,
        'return' => 'ids' ,
        )
    );

    $vouchersLink = ifgtapifwoo_getVouchersPDF($order_ids, $pagetypePDF);

    if ($vouchersLink) {

        wp_redirect( $vouchersLink );
        exit;

    } else {

       wp_redirect( admin_url('admin.php?page=geniki_admin_menu&error=auth') );
    }

}



?>