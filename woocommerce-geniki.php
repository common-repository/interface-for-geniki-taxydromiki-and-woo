<?php
/*
  Plugin Name: Interface for Geniki Taxydromiki API v2 and Woo
  Description: Provides interface with Geniki Taxydromiki web service API (v2) for Woocommerce
  Version: 1.0.2
  Author: web-panda.gr 
  Author URI: https://web-panda.gr
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

load_plugin_textdomain('geniki-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');

//Add auth file
include plugin_dir_path( __FILE__ ) . 'geniki-auth.php';

//Add helper functions file
include plugin_dir_path( __FILE__ ) . 'helper-functions.php';

//Add main functions file
include plugin_dir_path( __FILE__ ) . 'main-functions.php';

//Add metabox file
include plugin_dir_path( __FILE__ ) . 'geniki-metabox.php';


//Enque Font-Awesome script
function ifgtapifwoo_enqueue_script() {   
    wp_enqueue_script( 'font-awesome', 'https://kit.fontawesome.com/56a47f9813.js' );
}
add_action('admin_enqueue_scripts', 'ifgtapifwoo_enqueue_script');


//ADMIN MENUS etc.
include plugin_dir_path( __FILE__ ) . 'orders-column.php';
include plugin_dir_path( __FILE__ ) . 'options.php';
include plugin_dir_path( __FILE__ ) . 'bulk-actions.php';
include plugin_dir_path( __FILE__ ) . 'track-and-trace.php';


//Function below executes when a new order is made
add_action('woocommerce_thankyou', 'ifgtapifwoo_autogenerate_voucher', 10, 1);
function ifgtapifwoo_autogenerate_voucher( $order_id ) {

  $autogenerate = get_option('autogenerate');

  if ($autogenerate == 1) {
    ifgtapifwoo_generateVoucher( $order_id );
  }

}


add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'ifgtapifwoo_add_plugin_page_settings_link');
function ifgtapifwoo_add_plugin_page_settings_link( $links ) {
  $links[] = '<a href="' .
    admin_url( 'admin.php?page=geniki_admin_menu' ) .
    '">' . __('Settings') . '</a>';
  return $links;
}

