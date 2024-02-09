<?php
/*
Plugin Name: WooCommerce Flash Sale
Description: A WooCommerce plugin for scheduling and managing flash sales, tailored specifically for use in Laku6 Ecommerce.
Version: 1.0.4
Author: Deviant96
Text Domain: woocommerce-flash-sale
*/

if (!defined('ABSPATH')) {
    exit;
}

define('PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugin_dir_url(__FILE__));

// require_once(plugin_dir_path(__FILE__) . 'includes/flash-sale-functions.php');

require_once(PLUGIN_DIR . 'includes/admin/admin.php');
require_once(PLUGIN_DIR . 'includes/frontend/frontend.php');
require_once(PLUGIN_DIR . 'includes/helpers/helper-functions.php');

register_activation_hook(__FILE__, 'my_woocommerce_plugin_activate');
register_deactivation_hook(__FILE__, 'my_woocommerce_plugin_deactivate');

function my_woocommerce_plugin_activate()
{
}

function my_woocommerce_plugin_deactivate()
{
}