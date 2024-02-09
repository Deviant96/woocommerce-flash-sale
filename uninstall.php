<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove options
delete_option('flash_sale_products');
delete_option('flash_sale_products_discount');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}flash_sale_products_discount");
