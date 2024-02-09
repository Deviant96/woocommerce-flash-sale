<?php
if (!defined('ABSPATH')) {
    exit;
}

function flash_sale_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('flash_sale_messages', 'flash_sale_message', __('Settings Saved', 'woocommerce-flash-sale'), 'updated');
    }

    settings_errors('flash_sale_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
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

add_action('admin_menu', 'flash_sale_settings_menu');

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

function flash_sale_settings_init()
{
    add_settings_section(
        'flash_sale_section',
        __('Flash Sale Settings', 'woocommerce-flash-sale'),
        'flash_sale_settings_section_callback',
        'flash_sale_settings'
    );

    add_settings_field(
        'flash_sale_enable',
        __('Enable Flash Sale', 'woocommerce-flash-sale'),
        'flash_sale_enable_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_enable', 'flash_sale_sanitize_enable');

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

    add_settings_field(
        'flash_sale_text',
        __('Flash Sale Text', 'woocommerce-flash-sale'),
        'flash_sale_text_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_text', 'sanitize_textarea_field');

    add_settings_field(
        'flash_sale_products',
        __('Products for Flash Sale', 'woocommerce-flash-sale'),
        'flash_sale_products_callback',
        'flash_sale_settings',
        'flash_sale_section'
    );
    register_setting('flash_sale_settings', 'flash_sale_products', 'flash_sale_sanitize_product_ids');

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
        echo '<input type="number" name="flash_sale_products_discount[' . $product_id . ']" min="0" max="100" step="1" value="' . esc_attr($product_discount) . '" class="small-text" /> %';
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
    return ($input === 'yes') ? 'yes' : 'no';
}

function flash_sale_settings_section_callback()
{
    echo '<p>' . __('Configure the flash sale settings.', 'woocommerce-flash-sale') . '</p>';
}

function flash_sale_start_date_callback()
{
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    echo '<input type="datetime-local" name="flash_sale_start_date" value="' . esc_attr($flash_sale_start_date) . '" />';
}

function flash_sale_end_date_callback()
{
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    echo '<input type="datetime-local" name="flash_sale_end_date" value="' . esc_attr($flash_sale_end_date) . '" />';
}

function flash_sale_enqueue_select2()
{
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');

    wp_enqueue_script('flash-sale-admin', plugin_dir_url(__FILE__) . '../js/flash-sale-admin.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'flash_sale_enqueue_select2');

add_action('wp_enqueue_scripts', 'flash_sale_enqueue_scripts');
function flash_sale_enqueue_scripts()
{
    wp_enqueue_style('flash-sale-styles', plugin_dir_url(__FILE__) . '../css/style.min.css', array(), '1.0');
}

function flash_sale_show_all_link() {
    $flash_sale_show_all_page_id = get_option('flash_sale_show_all_page', 0);
    if ($flash_sale_show_all_page_id) {
        $page_url = get_permalink($flash_sale_show_all_page_id);
        echo '<a class="flash-sale-show-all" href="' . esc_url($page_url) . '" class="flash-sale-show-all-link">Show All</a>';
    }
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
add_action('woocommerce_update_options_products', 'save_flash_sale_settings');

function save_flash_sale_settings()
{
    $flash_sale_start_date = isset($_POST['flash_sale_start_date']) ? sanitize_text_field($_POST['flash_sale_start_date']) : '';
    $flash_sale_end_date = isset($_POST['flash_sale_end_date']) ? sanitize_text_field($_POST['flash_sale_end_date']) : '';
    $flash_sale_products = isset($_POST['flash_sale_products']) ? array_map('intval', $_POST['flash_sale_products']) : array();

    update_option('flash_sale_start_date', $flash_sale_start_date);
    update_option('flash_sale_end_date', $flash_sale_end_date);
    update_option('flash_sale_products', $flash_sale_products);
}

// Display countdown timer on the shop and single product pages during the flash sale period
add_action('woocommerce_before_single_product', 'display_flash_sale_countdown_two');
add_action('woocommerce_before_shop_loop', 'display_flash_sale_countdown_two');

function display_flash_sale_countdown_two()
{
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    $flash_sale_products = get_option('flash_sale_products', array());
    echo "Display flash sale countdown";

    if (!empty($flash_sale_start_date) && !empty($flash_sale_end_date) && in_array(get_the_ID(), $flash_sale_products)) {
        echo "Yes this product is included in flash sale!";
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            echo "Flash sale still active!";
            $time_remaining = $end_date - $now;

            echo '<div class="flash-sale-countdown">';
            echo '<h3>Flash Sale Ends In:</h3>';
            echo '<p id="flash-sale-countdown-timer"></p>';
            echo '</div>';
            echo plugin_dir_url(__FILE__);

            wp_enqueue_script('flash-sale-countdown-timer', plugin_dir_url(__FILE__) . '../js/countdown-timer.min.js');

            wp_localize_script('flash-sale-countdown-timer', 'flash_sale_time_remaining', $time_remaining);
            wp_localize_script('flash-sale-countdown-timer', 'flash_sale_end_date', $flash_sale_end_date);
        }
    }
}

function display_flash_sale_countdown()
{
    $flash_sale_enable = get_option('flash_sale_enable', 'yes');
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_text = get_option('flash_sale_text', '');

    $products = array();
    foreach ($flash_sale_products as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $products[] = $product;
        }
    }

    if ($flash_sale_enable === 'yes' && !empty($flash_sale_start_date) && !empty($flash_sale_end_date) && !empty($products)) {
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            wp_enqueue_script('flash-sale-countdown-timer', plugin_dir_url(__FILE__) . '../js/countdown-timer.min.js', array('jquery'), '1.0', true);
            wp_localize_script('flash-sale-countdown-timer', 'flash_sale_time_remaining', $end_date - $now);
            wp_localize_script('flash-sale-countdown-timer', 'flash_sale_end_date', $flash_sale_end_date);
            $countdown_format = 'HH:mm:ss'; // hour:minute:seconds
            wp_localize_script('flash-sale-countdown-timer', 'flash_sale_countdown_format', $countdown_format);

            echo '<div id="flash-sale">';
            echo '<div>';
            echo '<div class="flash-sale-countdown">';
            echo '<div class="flash-sale-text">' . wpautop($flash_sale_text) . '</div>';
            echo '<div class="flash-sale-indicator">';
            echo '<p class="flash-sale-expired-text">Berakhir dalam';
            echo '<span id="flash-sale-countdown-timer">00:00:00</span>';
            echo '</p>';

            flash_sale_show_all_link();

            echo '</div>';
            echo '</div>';

            display_flash_sale_products($products);

            echo '</div>';
            echo '</div>';
        }
    }
}

function display_flash_sale_products($products)
{
    $flash_sale_percentage_discount = get_option('flash_sale_percentage_discount', 0);
    $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

    if (!empty($products)) {
        echo '<div class="flash-sale-products">';
        // echo '<div class="related">';
        echo '<div class="products">';
        foreach ($products as $product) {
            $product_id = $product->get_id();
            $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;
            $discounted_price = ($product->get_price() * (100 - $product_discount)) / 100;
            
            $post_object = get_post($product->get_id());

            setup_postdata($GLOBALS['post'] =  &$post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

            include PLUGIN_DIR_PATH . 'content-product-flash-sale.php';
        }
        echo '</div>';
        // echo '</div>';
        echo '</div>';
    }
    wp_reset_postdata();
}

// Add a shortcode to display flash sale products
add_shortcode('flash_sale_products', 'flash_sale_products_shortcode');
function flash_sale_products_shortcode($atts)
{
    $flash_sale_products = get_option('flash_sale_products', array());

    $products = array();
    foreach ($flash_sale_products as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $products[] = $product;
        }
    }

    ob_start();
    display_flash_sale_products($products);
    return ob_get_clean();
}


function flash_sale_products_secondary_shortcode() {
    ob_start();
    lk6_get_template_part('template-flash-sale-products');
    return ob_get_clean();
}
add_shortcode('flash_sale_products_secondary', 'flash_sale_products_secondary_shortcode');


// Display the flash sale discounted price on the product details page
function display_flash_sale_discounted_price() {
    global $product;

    $product_id = $product->get_id();
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_products_discount = get_option('flash_sale_products_discount', array());
    $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;

    if (in_array($product_id, $flash_sale_products)) {
        $regular_price = $product->get_regular_price();
        $discounted_price = ($regular_price * (100 - $product_discount)) / 100;

        echo '<div class="flash-sale-product-price">';
        echo '<span class="flash-sale-regular-price">' . wc_price($regular_price) . '</span>';
        echo '<span class="flash-sale-percentage-discount"> (' . $product_discount . '% OFF)</span>';
        echo '<span class="flash-sale-discounted-price">' . wc_price($discounted_price) . '</span>';
        echo '</div>';
    }
}
add_action('woocommerce_single_product_summary', 'display_flash_sale_discounted_price', 3);



// Hook to apply flash sale discounts to cart
function apply_flash_sale_discounts_to_cart() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!is_user_logged_in()) {
        return;
    }

    if (!is_cart() && !is_checkout()) {
        return;
    }

    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

    // Loop through cart items
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];

        if (in_array($product_id, $flash_sale_products)) {
            $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;
            $discounted_price = $cart_item['data']->get_price() * (1 - ($product_discount / 100));

            // Apply discount to the cart item price
            $cart_item['data']->set_price($discounted_price);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'apply_flash_sale_discounts_to_cart');


/**
 * The below function will help to load template file from plugin directory of wordpress
 *  Extracted from : http://wordpress.stackexchange.com/questions/94343/get-template-part-from-plugin
 */

function lk6_get_template_part($slug, $name = null)
{

    do_action("lk6_get_template_part_{$slug}", $slug, $name);

    $templates = array();
    if (isset($name)) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";
    // var_dump(lk6_get_template_path($templates, true, false));
    lk6_get_template_path($templates, true, false);
}

/* Extend locate_template from WP Core
 * Define a location of your plugin file dir to a constant in this case = PLUGIN_DIR_PATH
 * Note: PLUGIN_DIR_PATH - can be any folder/subdirectory within your plugin files
 */

function lk6_get_template_path($template_names, $load = false, $require_once = true)
{
    $located = '';
    // var_dump(PLUGIN_DIR_PATH);
    // echo "template path";
    foreach ((array) $template_names as $template_name) {
        // echo $template_name;
        if (!$template_name) {
            continue;
        }

        /* search file within the PLUGIN_DIR_PATH only */
        if (file_exists(PLUGIN_DIR_PATH . $template_name)) {
            // echo "ada";
            $located = PLUGIN_DIR_PATH . $template_name;
            // echo $located;
            break;
        }
    }

    if ($load && '' != $located) {
        load_template($located, $require_once);
    }

    return $located;
}

/**
 * Truncate text by characters
 *
 * @param $text String - text to truncate
 * @param $chars Integer - number of characters to truncate to - default 40
 * @param $breakWord Boolean - if true, will break on word boundaries - when false, could lead to strings longer than $chars
 * @param $ellipsis String - if set, will append to truncated text, '…' character by default
 */
function truncateTextByChars(
    $text,
    $chars = 40,
    $breakWord = false,
    $ellipsis = '…') {
    if (empty($text)) {
        return null;
    }

    if ($breakWord) {
        $truncate = substr($text, 0, $chars);
        return $ellipsis && strlen($truncate) < strlen($text)
        ? $truncate . $ellipsis
        : $truncate;
    }

    if (strlen($text) > $chars) {
        $shortened = (substr($text, 0, strpos($text, ' ', $chars)));
        $final = $ellipsis && strlen($shortened) > 0
        ? $shortened . $ellipsis
        : $text;
    } else {
        $final = $text;
    }
    return $final;
}


function flash_sale_infinite_scroll_scripts()
{
	if( is_page('flash-sale') ) {
		wp_enqueue_script(
			'masonry-js', 
			get_template_directory_uri() . '/assets/js/masonry.pkgd.min.js', 
			array( 'jquery' ), 
			'', 
			true 
		);
		wp_enqueue_script(
			'images-loaded-js', 
			get_template_directory_uri() . '/assets/js/imagesloaded.pkgd.min.js', 
			array( 'masonry-js' ), 
			'', 
			true 
		);
		wp_enqueue_script(
			'init-masonry-js', 
			get_template_directory_uri() . '/assets/js/init-masonry-on-single-product.js', 
			array( 'images-loaded-js' ), 
			'', 
			true 
		);
	}

	if ( is_singular() ) {
		wp_enqueue_script(
			'laku6-infinite-scroll-single-js', 
			get_template_directory_uri() . '/assets/js/infinite-scroll-single.min.js', 
			array( 'jquery', 'init-masonry-js' ), 
			'', 
			true 
		);
	}
}
add_action('wp_enqueue_scripts', 'flash_sale_infinite_scroll_scripts');