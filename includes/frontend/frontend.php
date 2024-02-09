<?php
if (!defined('ABSPATH')) {
    exit;
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

add_action('wp_enqueue_scripts', 'flash_sale_enqueue_scripts');
function flash_sale_enqueue_scripts()
{
    wp_enqueue_style('flash-sale-styles', PLUGIN_URL . 'assets/css/style.min.css', array(), '1.0');
}


function flash_sale_show_all_link() {
    $flash_sale_show_all_page_id = get_option('flash_sale_show_all_page', 0);
    if ($flash_sale_show_all_page_id) {
        $page_url = get_permalink($flash_sale_show_all_page_id);
        echo '<a class="flash-sale-show-all" href="' . esc_url($page_url) . '" class="flash-sale-show-all-link">Show All</a>';
    }
}


// Display countdown timer on the shop and single product pages during the flash sale period
function display_flash_sale_countdown_two()
{
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_text = get_option('flash_sale_text', '');

    if (!empty($flash_sale_start_date) && !empty($flash_sale_end_date) && in_array(get_the_ID(), $flash_sale_products)) {
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            $time_remaining = $end_date - $now;

            echo '<div id="flash-sale-pdp">';
            echo '<div>';
            echo '<div class="flash-sale-countdown-pdp">';
            echo '<div class="flash-sale-text">' . wpautop($flash_sale_text) . '</div>';
            echo '<div class="flash-sale-indicator">';
            echo '<p class="flash-sale-expired-text">Berakhir dalam';
            echo '<span id="flash-sale-countdown-timer">00:00:00</span>';
            echo '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            wp_enqueue_script('flash-sale-countdown-timer', PLUGIN_URL . 'assets/js/countdown-timer.min.js', array('jquery'), '1.0', true);

            // Localize script with time remaining and end date
            // wp_localize_script('flash-sale-countdown-timer', 'flash_sale_time_remaining', $time_remaining);
            // wp_localize_script('flash-sale-countdown-timer', 'flash_sale_end_date', $flash_sale_end_date);

            $countdown_format = 'HH:mm:ss'; // hour:minute:seconds
            $script  = 'flash_sale_countdown_format = '. json_encode($countdown_format) .'; ';
            $script .= 'flash_sale_time_remaining = '. json_encode($end_date - $now) .'; ';
            $script .= 'flash_sale_end_date = '. json_encode($flash_sale_end_date) .'; ';
            
            wp_add_inline_script('flash-sale-countdown-timer', $script, 'before');
        }
    }
}
add_action('woocommerce_before_single_product', 'display_flash_sale_countdown_two');
add_action('woocommerce_before_shop_loop', 'display_flash_sale_countdown_two');


function is_flash_sale_active() 
{
    $flash_sale_enable = get_option('flash_sale_enable', 'yes');
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');

    if ($flash_sale_enable === 'yes' && !empty($flash_sale_start_date) && !empty($flash_sale_end_date)) {
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            return true;
        }
    }

    return false;
}

// Modify the display_flash_sale_countdown function
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
            wp_enqueue_script('flash-sale-countdown-timer', PLUGIN_URL . 'assets/js/countdown-timer.min.js', array('jquery'), '1.0', true);

            $countdown_format = 'HH:mm:ss'; // hour:minute:seconds
            $script  = 'flash_sale_countdown_format = '. json_encode($countdown_format) .'; ';
            $script .= 'flash_sale_time_remaining = '. json_encode($end_date - $now) .'; ';
            $script .= 'flash_sale_end_date = '. json_encode($flash_sale_end_date) .'; ';
            
            wp_add_inline_script('flash-sale-countdown-timer', $script, 'before');

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

// New function to display flash sale products
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
            $discounted_price = ($product->get_regular_price() * (100 - $product_discount)) / 100;
            
            $post_object = get_post($product->get_id());

            setup_postdata($GLOBALS['post'] =  &$post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

            include PLUGIN_DIR . 'templates/content-product-flash-sale.php';
        }
        echo '</div>';
        // echo '</div>';
        echo '</div>';
    }
    wp_reset_postdata();
}

// Add a shortcode to display flash sale products
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
add_shortcode('flash_sale_products', 'flash_sale_products_shortcode');


function flash_sale_products_secondary_shortcode() {
    ob_start();
    lk6_get_template_part('template-flash-sale-products');
    return ob_get_clean();
}
add_shortcode('flash_sale_products_secondary', 'flash_sale_products_secondary_shortcode');


// Display the flash sale discounted price on the product details page
function display_flash_sale_discounted_price() {
    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_text = get_option('flash_sale_text', '');

    if (!empty($flash_sale_start_date) && !empty($flash_sale_end_date) && in_array(get_the_ID(), $flash_sale_products)) {
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            $time_remaining = $end_date - $now;

            global $product;

            $product_id = $product->get_id();
            $flash_sale_products = get_option('flash_sale_products', array());
            $flash_sale_products_discount = get_option('flash_sale_products_discount', array());
            $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;

            if (in_array($product_id, $flash_sale_products)) {
                $regular_price = $product->get_regular_price();
                $discounted_price = ($regular_price * (100 - $product_discount)) / 100;
                echo '<div class="flash-sale-product-price">';
                echo '<span class="flash-sale-discounted-price">' . wc_price($discounted_price) . '</span>';
                echo '<div class="flash-sale-discount">';
                echo '<span class="flash-sale-percentage-discount">' . $product_discount . '%</span>';
                echo '<del class="flash-sale-regular-price">' . wc_price($regular_price) . '</del>';
                echo '</div>';
                echo '</div>';
            }
        }
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

    $flash_sale_start_date = get_option('flash_sale_start_date', '');
    $flash_sale_end_date = get_option('flash_sale_end_date', '');
    $flash_sale_products = get_option('flash_sale_products', array());
    $flash_sale_text = get_option('flash_sale_text', '');

    if (!empty($flash_sale_start_date) && !empty($flash_sale_end_date)) {
        $now = current_time('timestamp');
        $start_date = strtotime($flash_sale_start_date);
        $end_date = strtotime($flash_sale_end_date);

        if ($now >= $start_date && $now <= $end_date) {
            $time_remaining = $end_date - $now;

            $flash_sale_products = get_option('flash_sale_products', array());
            $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];

                if (in_array($product_id, $flash_sale_products)) {
                    $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;
                    $discounted_price = $cart_item['data']->get_regular_price() * (1 - ($product_discount / 100));

                    $cart_item['data']->set_price($discounted_price);
                }
            }
        }
    }

    
}
add_action('woocommerce_before_calculate_totals', 'apply_flash_sale_discounts_to_cart');


// Filter the WooCommerce product price to use the flash sale discounted price if available
function filter_woocommerce_product_get_price_html($price, $product) {
    if (is_admin()) {
        return $price; // Do not filter prices in the admin area
    }

    if (!$product) {
        return $price;
    }

    if(is_shop() || is_product_category() || is_product_tag()) {

        $flash_sale_products = get_option('flash_sale_products', array());
        $sale_price = $product->get_sale_price();

        if (is_flash_sale_active() && in_array(get_the_ID(), $flash_sale_products)) {

            $product_id = $product->get_id();
            $flash_sale_products = get_option('flash_sale_products', array());
            $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

            if (in_array($product_id, $flash_sale_products)) {
                $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;
                $discounted_price = ($product->get_regular_price() * (100 - $product_discount)) / 100;

                $sale_price = (int)$discounted_price;

                if ($product->is_type('simple')) {
                    $regular_price = $product->get_regular_price();
                    $price_amt = $product->get_price();
                    return just_the_price($price_amt, $regular_price, $sale_price);
                }
            }
        }
    }

    return $price;
}
add_filter('woocommerce_get_price_html', 'filter_woocommerce_product_get_price_html', 101, 2);
// add_filter( 'woocommerce_product_get_regular_price', 'filter_woocommerce_product_get_price', 10, 2 );
// add_filter( 'woocommerce_product_get_price', 'filter_woocommerce_product_get_price', 101, 2 );
// add_filter( 'woocommerce_product_variation_get_regular_price', 'filter_woocommerce_product_get_price', 10, 2 );


// Filter the WooCommerce product price to use the flash sale discounted price if available
function filter_woocommerce_product_get_price($price, $product) {
    if (is_admin()) {
        return $price; // Do not filter prices in the admin area
    }

    if (!$product) {
        return $price;
    }

    if(!is_product()) {
        return $price;
    }

    $flash_sale_products = get_option('flash_sale_products', array());

    if (is_flash_sale_active() && in_array(get_the_ID(), $flash_sale_products)) {

        $product_id = $product->get_id();
        $flash_sale_products = get_option('flash_sale_products', array());
        $flash_sale_products_discount = get_option('flash_sale_products_discount', array());

        if (in_array($product_id, $flash_sale_products)) {
            $product_discount = isset($flash_sale_products_discount[$product_id]) ? $flash_sale_products_discount[$product_id] : 0;
            $discounted_price = ($product->get_regular_price() * (100 - $product_discount)) / 100;

            return $discounted_price;
        }
    }

    return $price;
}
add_filter('woocommerce_product_get_price', 'filter_woocommerce_product_get_price', 10, 2);


if (!function_exists('just_the_price')) {

	function just_the_price($price_amt, $regular_price, $flash_sale_price)
	{
		// $html_price = '<p class="price">';
		$html_price = '';
		// If product is in sale
		if ($flash_sale_price != 0) {
			if(is_single()) {
				$html_price .= wc_price($flash_sale_price);
				$html_price .= '<div class=disc>';
				$html_price .= '<div class=disc-percent>';
				$html_price .= '<p>' . sale_badge_percentage($flash_sale_price) . '</p>';
				$html_price .= '</div>';

				$html_price .= '<p class=disc-stright>' . wc_price($regular_price) .'</p>';
				$html_price .= '</div>';
			}
			else {
				$html_price .= wc_price($flash_sale_price);
				$html_price .= '<div class=p-item-disc-w>';
				$html_price .= '<div class=p-item-disc>';
				$html_price .= '<p class=p-item-disc-text>' . sale_badge_percentage($flash_sale_price) . '</p>';
				$html_price .= '</div>';

				$html_price .= '<p class=p-item-disc-then>' . wc_price($regular_price) .'</p>';
				$html_price .= '</div>';
			}
			
		}
		// In sale but free
		else if (($price_amt == $flash_sale_price) && ($flash_sale_price == 0)) {
			$html_price .= '<ins>Free!</ins>';
			$html_price .= '<del>' . wc_price($regular_price) . '</del>';
		}
		// Not in sale
		else if (($price_amt == $regular_price) && ($regular_price != 0)) {
			$html_price .= '<p class="laku6-notinsale p-item-price">' . wc_price($regular_price) . '</p>';
		}
		// For free product
		else if (($price_amt == $regular_price) && ($regular_price == 0)) {
			$html_price .= '<p class="laku6-free p-item-price">Free!</p>';
		}
		// $html_price .= '</p>';
		return $html_price;
	}
}


if (!function_exists('sale_badge_percentage')) {
    function sale_badge_percentage(int $flash_sale_price)
    {
        global $product;
        if ($product->is_type('simple')) {
            $max_percentage = (($product->get_regular_price() - $flash_sale_price) / $product->get_regular_price()) * 100;
        } elseif ($product->is_type('variable')) {
            $max_percentage = 0;
            foreach ($product->get_children() as $child_id) {
                $variation = wc_get_product($child_id);
                $price = $variation->get_regular_price();
                $sale = $flash_sale_price;
                if ($price != 0 && !empty($sale)) {
                    $percentage = ($price - $sale) / $price * 100;
                }
                if ($percentage > $max_percentage) {
                    $max_percentage = $percentage;
                }
            }
        }
        if ($max_percentage > 0) {
            return round($max_percentage) . "%";
        }
    }
}















