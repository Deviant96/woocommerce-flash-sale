<?php
if (!defined('ABSPATH')) {
    exit;
}

function flash_sale_plugin_add_capabilities() {
    $role = get_role('shop_manager');
    $role->add_cap('manage_flash_sale_plugin_settings');
    $role->add_cap('manage_options');
    $role2 = get_role('administrator');
    $role2->add_cap('manage_flash_sale_plugin_settings');
}
add_action('admin_init', 'flash_sale_plugin_add_capabilities');

function flash_sale_settings_link($links) {
    $settings_link = '<a href="admin.php?page=flash_sale_settings">' . __('Settings', 'woocommerce-flash-sale') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flash_sale_settings_link');


function flash_sale_enqueue_select2()
{
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');

    wp_enqueue_script('flash-sale-admin', PLUGIN_URL . 'assets/js/flash-sale-admin.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'flash_sale_enqueue_select2');

function flash_sale_settings_page()
{
    if (!current_user_can('manage_flash_sale_plugin_settings')) {
        wp_die('You do not have permission to access this page.');
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('flash_sale_messages', 'flash_sale_message', __('Settings Saved', 'woocommerce-flash-sale'), 'updated');
    }

    settings_errors('flash_sale_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="<?php echo admin_url('options.php'); ?>" method="post">
            <?php
                settings_fields('flash_sale_settings');
                do_settings_sections('flash_sale_settings');
                submit_button(__('Save Settings', 'woocommerce-flash-sale'));
            ?>
        </form>

        <h2>Flash Sale Products Page</h2>
        <p>Use the following shortcode to display all flash sale products on a page:</p>
        <code>[flash_sale_products]</code>
    </div>
    <?php
}

function flash_sale_settings_menu()
{
    add_submenu_page(
        'woocommerce',
        __('Flash Sale Settings', 'woocommerce-flash-sale'),
        __('Flash Sale', 'woocommerce-flash-sale'),
        'manage_options',
        'flash_sale_settings',
        'flash_sale_settings_page'
    );

    add_action('admin_init', 'flash_sale_settings_init');
}
add_action('admin_menu', 'flash_sale_settings_menu');

function flash_sale_settings_init()
{
    add_settings_section(
        'flash_sale_section',
        __('Flash Sale Settings', 'woocommerce-flash-sale'),
        'flash_sale_settings_section_callback',
        'flash_sale_settings'
    );

    // Add a new field for enabling or disabling the flash sale
    add_settings_field(
        'flash_sale_enable',
        __('Enable Flash Sale', 'woocommerce-flash-sale'),
        'flash_sale_enable_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_enable', 'flash_sale_sanitize_enable');

    // Add a new field for 'Show All' page
    add_settings_field(
        'flash_sale_show_all_page',
        __('Flash Sale "Show All" Page', 'woocommerce-flash-sale'),
        'flash_sale_show_all_page_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_show_all_page', 'absint');

    add_settings_field(
        'flash_sale_start_date',
        __('Flash Sale Start Date', 'woocommerce-flash-sale'),
        'flash_sale_start_date_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_start_date', 'sanitize_text_field');

    add_settings_field(
        'flash_sale_end_date',
        __('Flash Sale End Date', 'woocommerce-flash-sale'),
        'flash_sale_end_date_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_end_date', 'sanitize_text_field');

    // Add a new field for flash sale text
    add_settings_field(
        'flash_sale_text',
        __('Flash Sale Text', 'woocommerce-flash-sale'),
        'flash_sale_text_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_text', 'sanitize_textarea_field');

    // Add a new field for product selection using the product selector
    add_settings_field(
        'flash_sale_products',
        __('Products for Flash Sale', 'woocommerce-flash-sale'),
        'flash_sale_products_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_products', 'flash_sale_sanitize_product_ids');

    // Add a new field for setting the discount percentage for each product
    add_settings_field(
        'flash_sale_products_discount',
        __('Flash Sale Products Discount', 'woocommerce-flash-sale'),
        'flash_sale_products_discount_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_products_discount', 'flash_sale_sanitize_products_discount');
}


function flash_sale_products_discount_callback() {
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ),
        ),
        'tax_query' => array(
            array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            ),
        ),
    );
    $products = get_posts($product_args);

    echo '<div class="flash-sale-products-discount-table">';
    echo '<table class="form-table">';
    echo '<tbody>';

    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_name = $product->post_title;
        $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;

        echo '<tr class="flash-sale-product-row" data-product-id="' . esc_attr($product_id) . '">';
        echo '<th scope="row">' . esc_html($product_name) . '</th>';
        echo '<td>';
        echo '<input type="number" class="flash_sale_percentage_discount" name="flash_sale_products_discount[' . $product_id . ']" min="0" max="100" step="1" value="' . esc_attr($product_discount) . '" class="small-text" /> <span>%</span>';
        echo '<p class="warning-message">Percentage discount must be between 0 and 100.</p>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}


function flash_sale_sanitize_products_discount($input) {
    $sanitized_input = array();
    foreach ($input as $product_id => $discount) {
        $sanitized_input[$product_id] = absint($discount);
    }
    return $sanitized_input;
}


function flash_sale_show_all_page_callback() {
    $flash_sale_show_all_page = get_option('flash_sale_show_all_page', 0);
    $pages = get_pages();

    echo '<select name="flash_sale_show_all_page">';
    echo '<option value="0">Select a Page</option>';
    foreach ($pages as $page) {
        $selected = selected($flash_sale_show_all_page, $page->ID, false);
        echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}


function flash_sale_text_callback()
{
    $flash_sale_text = get_option('flash_sale_text', '');
    echo '<textarea name="flash_sale_text" rows="5" cols="50">' . esc_textarea($flash_sale_text) . '</textarea>';
}


function flash_sale_enable_callback()
{
    $flash_sale_enable = get_option('flash_sale_enable', 'yes');
    echo '<input type="checkbox" name="flash_sale_enable" value="yes" ' . checked('yes', $flash_sale_enable, false) . ' />';
}


function flash_sale_sanitize_enable($input)
{
    return ($input === 'yes') ? 'yes' : 'no'; // Sanitize the input to either 'yes' or 'no'.
}


function flash_sale_settings_section_callback()
{
    echo '<p>' . __('Configure the flash sale settings.', 'woocommerce-flash-sale') . '</p>';
}


function flash_sale_start_date_callback()
{
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    echo '<input type="datetime-local" name="flash_sale_start_date" id="flash_sale_start_date" value="' . esc_attr($flash_sale_start_date) . '" />';
}


function flash_sale_end_date_callback()
{
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    echo '<input type="datetime-local" name="flash_sale_end_date" id="flash_sale_end_date" value="' . esc_attr($flash_sale_end_date) . '" />';
    echo '<p class="warning-message">End date cannot be earlier than the start date.</p>';
}


function flash_sale_products_callback()
{
    $flash_sale_products = get_option('flash_sale_products', array());
    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ),
        ),
        'tax_query' => array(
            array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            ),
        ),
    );
    $products = get_posts($product_args);

    echo '<select name="flash_sale_products[]" multiple="multiple" class="flash-sale-products-select">';
    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_sku = get_post_meta($product_id, '_sku', true);
        $product_name = get_the_title($product_id);
        $truncated_product_name = truncateTextByChars($product_name, 25, true);
        $selected = in_array($product_id, $flash_sale_products) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($product_id) . '" ' . $selected . '>' . esc_html($product_sku . ' - ' . $product_name) . '</option>';
    }
    echo '</select>';

    // Initialize Select2
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('.flash-sale-products-select').select2();
        });
    </script>
    <?php
}


function flash_sale_sanitize_product_ids($input)
{
    if (!is_array($input)) {
        return array();
    }

    return array_map('intval', $input);
}


// Save flash sale settings when the WooCommerce settings are saved
function save_flash_sale_settings()
{
    $flash_sale_start_date = isset($_POST['flash_sale_start_date']) ? sanitize_text_field($_POST['flash_sale_start_date']) : '';
    $flash_sale_end_date = isset($_POST['flash_sale_end_date']) ? sanitize_text_field($_POST['flash_sale_end_date']) : '';
    $flash_sale_products = isset($_POST['flash_sale_products']) ? array_map('intval', $_POST['flash_sale_products']) : array();

    update_option('flash_sale_start_date', $flash_sale_start_date);
    update_option('flash_sale_end_date', $flash_sale_end_date);
    update_option('flash_sale_products', $flash_sale_products);
}
add_action('woocommerce_update_options_products', 'save_flash_sale_settings');


function add_flash_sale_exclusion_field() {
    woocommerce_wp_checkbox(
        array(
            'id'          => 'exclude_flash_sale_products',
            'label'       => __('Exclude from Flash Sale Products', 'woocommerce'),
            'description' => __( 'Whether the usage of this coupon should be excluded for product currently in flash sale', 'woocommerce' ),  
            'desc_tip'    => false
        )
    );

    woocommerce_wp_checkbox(
        array(
            'id'          => 'restrict_to_flash_sale_products',
            'label'       => __('Restrict to Flash Sale Products', 'woocommerce'),
            'description' => __( 'Whether the usage of this coupon should only be restricted for product currently in flash sale', 'woocommerce' ),  
            'desc_tip'    => false
        )
    );
}
add_action('woocommerce_coupon_options_usage_restriction', 'add_flash_sale_exclusion_field');


function save_flash_sale_exclusion_field($coupon_id) {
    $exclude_flash_sale_products = isset($_POST['exclude_flash_sale_products']) ? 'yes' : 'no';
    update_post_meta($coupon_id, 'exclude_flash_sale_products', $exclude_flash_sale_products);

    $restrict_to_flash_sale_products = isset($_POST['restrict_to_flash_sale_products']) ? 'yes' : 'no';
    update_post_meta($coupon_id, 'restrict_to_flash_sale_products', $restrict_to_flash_sale_products);
}
add_action('woocommerce_coupon_options_save', 'save_flash_sale_exclusion_field');


function is_product_on_flash_sale($product_id) {
    $flash_sale_products = get_option('flash_sale_products', array());

    // Check if the product ID is in the list of flash sale products
    return in_array($product_id, $flash_sale_products);
}


function validate_coupon_exclusion_for_flash_sale($is_valid, $coupon) {
    $cart = WC()->cart;
    $exclude_flash_sale_products = get_post_meta($coupon->get_id(), 'exclude_flash_sale_products', true);

    if ($exclude_flash_sale_products === 'yes') {
        // Check if any product in the cart is on flash sale
        foreach ($cart->get_cart_contents() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (is_product_on_flash_sale($product_id)) {
                return false; // Disallow coupon if flash sale product is in the cart
            }
        }
    }

    return $is_valid;
}
add_filter('woocommerce_coupon_is_valid', 'validate_coupon_exclusion_for_flash_sale', 10, 2);


function validate_coupon_flash_sale_product_restriction($is_valid, $coupon) {
    if (!$is_valid) {
        return $is_valid; // Return if the coupon is already invalid
    }

    $cart = WC()->cart;
    $restrict_to_flash_sale_products = get_post_meta($coupon->get_id(), 'restrict_to_flash_sale_products', true);

    if ($restrict_to_flash_sale_products === 'yes') {
        // Check if any product in the cart is not on flash sale
        foreach ($cart->get_cart_contents() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (!is_product_on_flash_sale($product_id)) {
                return false; // Disallow coupon if a non-flash sale product is in the cart
            }
        }
    }

    return $is_valid;
}
add_filter('woocommerce_coupon_is_valid', 'validate_coupon_flash_sale_product_restriction', 10, 2);


