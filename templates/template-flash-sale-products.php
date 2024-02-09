<?php
/*
Template Name: Flash Sale Products
*/

get_header();

$args = array(
    'post_type' => 'product',
    'post__in' => get_option('flash_sale_products', array()),
    'posts_per_page' => -1,
);

$flash_sale_products_query = new WP_Query($args);
?>

<div class="container">
    <div id="product-wrapper" class="products">

        <?php if ($flash_sale_products_query->have_posts()) : ?>
                <?php while ($flash_sale_products_query->have_posts()) : $flash_sale_products_query->the_post(); ?>
                        <?php wc_get_template_part("content", "product-list-home"); ?>
                <?php endwhile; ?>
        <?php else : ?>
            <p>No flash sale products found.</p>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
