<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// var_dump($product);
if ( empty( $product ) || ! $product->is_visible() ) {

	return;
}

$tags = wp_get_post_terms( $product->get_id(), 'product_tag' );
$tag_names = '';
foreach ( $tags as $tag ) {
  $tag_names .= $tag->name . ', ';
}
$tag_names = rtrim( $tag_names, ', ' );

$get_brand = get_product_brand_name($product->get_id());

$category = wp_get_post_terms( $product->get_id(), 'product_cat' );
$get_category = $category[0]->name;

$terms = wp_get_post_terms( $product->get_id(), 'pa_grade_kondisi' );
    if ( $terms && ! is_wp_error( $terms ) ) {
        $grade = esc_html( $terms[0]->name );
    }
?>
<div <?php post_class(); ?>>
 <span class="product-loading"></span>

 	<div class="p-item">
	 	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			// do_action( 'woocommerce_before_shop_loop_item_title' );
			$output = '<div class="col-lg-4">';
			$size = 'thumbnail';

			if ( has_post_thumbnail() ) {               
				$output .= get_the_post_thumbnail( get_the_ID(  ), $size );
			} else {
				$output .= wc_placeholder_img( $size );
				// $output .= '<img src="' . woocommerce_placeholder_img_src() . '" alt="Placeholder" width="300px" height="300px" />';
			}                       
			$output .= '</div>';

			echo $output;
		?>
		<?php if (!isset($grade)): ?>
			<div class="p-grade-w">
				<div class="p-grade">
					<span><?php echo $grade; ?></span>
				</div>
			</div>
		<?php endif; ?>
		<div class="p-item-des">
			<p class="p-item-title">
				<?php 
					$truncated_product_name = truncateTextByChars(get_the_title(), 23, true);
					echo $truncated_product_name;
				?>
			</p>
			
			<?php

					echo '<p class="flash-sale-discounted-price">' . wc_price($discounted_price) . '</p>';
					echo '<p><span class="flash-sale-percentage-discount">' . $product_discount . '%</span>';
					echo '<span class="flash-sale-price">' . wc_price($product->get_regular_price()) . '</span></p>';
					/**
					 * woocommerce_after_shop_loop_item_title hook
					 *
					 * @hooked woocommerce_template_loop_rating - 5
					 * @hooked woocommerce_template_loop_price - 10 
					 */
					//  do_action( 'woocommerce_after_shop_loop_item_title' );
				?>

				<?php 
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
				 ?>


			<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

			<span class="product-details d-none" 
				data-product-details-name="<?php echo get_the_title(); ?>" 
				data-product-details-sku="<?php echo $product->get_sku(); ?>"
				data-product-details-price="<?php echo $product->get_price(); ?>"
				data-product-details-category="<?php echo $get_category; ?>"
				data-product-details-tags="<?php echo $tag_names; ?>"
				data-product-details-brand="<?php echo $get_brand; ?>"
				data-product-details-stocklevel="<?php echo $product->get_stock_quantity(); ?>"
			></span>
		</div>
		<a href="<?php esc_url(the_permalink()); ?>" class="p-item-link"></a>
	</div>
</div>
