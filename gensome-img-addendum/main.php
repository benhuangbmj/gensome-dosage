<?php
/**
 * Plugin Name: Gensome Img Addendum
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        add_action('admin_menu', function () {
            add_submenu_page(
                'edit.php?post_type=product',
                __('Product Addendum', 'gensome-img-addendum'),
                __('Product Addendum', 'gensome-img-addendum'),
                'manage_woocommerce',
                'product-addendum',
                'product_addendum_page_callback'
            );
        });
    } else {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>' . esc_html__('WooCommerce is not active. Please activate WooCommerce to use the Product Addendum feature.', 'gensome-img-addendum') . '</p></div>';
        });
    }
});

function product_addendum_page_callback()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Product Addendum', 'gensome-img-addendum'); ?></h1>
        <?php
        $args = [
            'limit' => -1,
            'status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ];

    $products = wc_get_products($args);
    if (!empty($products)) {
        ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column"><?php echo esc_html__('Name', 'gensome-img-addendum'); ?></th>
                        <th scope="col" class="manage-column"><?php echo esc_html__('Addendum Image', 'gensome-img-addendum'); ?></th>
                        <th scope="col" class="manage-column"><?php echo esc_html__('Selected Image URL', 'gensome-img-addendum'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                foreach ($products as $product) {
                    // Fetch the existing addendum post for the product
                    $existing_post = get_posts([
                        'post_type'   => 'addendum',
                        'post_parent' => $product->get_id(),
                        'numberposts' => 1,
                    ]);

                    $image_url = !empty($existing_post) ? $existing_post[0]->post_content : '';
                    ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_edit_post_link($product->get_id())); ?>">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <button class="button select-addendum" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                    <?php echo esc_html__('Select', 'gensome-img-addendum'); ?>
                                </button>
                            </td>
                            <td>
                                <span class="selected-image-url">
                                    <?php echo esc_html($image_url); ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                }
        ?>
                </tbody>
            </table>
            <?php
    } else {
        ?>
            <p><?php echo esc_html__('No products found.', 'gensome-img-addendum'); ?></p>
            <?php
    }
    ?>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_media(); // Enqueue WordPress Media Library
    wp_enqueue_script(
        'gensome-img-addendum-script',
        plugin_dir_url(__FILE__) . 'script.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('gensome-img-addendum-script', 'gensomeImgAddendum', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('gensome_img_addendum_nonce'),
    ]);
});

add_action('wp_ajax_save_addendum_image', function () {
    check_ajax_referer('gensome_img_addendum_nonce', 'nonce');

    $product_id = intval($_POST['product_id']);
    $image_url  = esc_url_raw($_POST['image_url']);

    if (!$product_id || !$image_url) {
        wp_send_json_error(['message' => __('Invalid data.', 'gensome-img-addendum')]);
    }

    // Check if an addendum post already exists for this product
    $existing_post = get_posts([
        'post_type'   => 'addendum',
        'post_parent' => $product_id,
        'numberposts' => 1,
    ]);

    if (!empty($existing_post)) {
        // Update the existing addendum post
        $post_id = $existing_post[0]->ID;
        wp_update_post([
            'ID'           => $post_id,
            'post_content' => $image_url,
        ]);
    } else {
        // Insert a new addendum post
        $post_id = wp_insert_post([
            'post_type'    => 'addendum',
            'post_title'   => 'Addendum for Product ' . $product_id,
            'post_content' => $image_url,
            'post_status'  => 'publish',
            'post_parent'  => $product_id,
        ]);
    }

    if ($post_id) {
        wp_send_json_success(['message' => __('Addendum saved successfully.', 'gensome-img-addendum')]);
    } else {
        wp_send_json_error(['message' => __('Failed to save addendum.', 'gensome-img-addendum')]);
    }
});

add_action('woocommerce_before_single_product_summary', function () {
    global $post;

    // Check if the current product has an addendum post
    $addendum_post = get_posts([
        'post_type'   => 'addendum',
        'post_parent' => $post->ID,
        'numberposts' => 1,
    ]);

    if (!empty($addendum_post)) {
        $addendum_image_url = esc_url($addendum_post[0]->post_content);

        if ($addendum_image_url) {
            ?>
            <style>
                .product-image-wrapper {
                    position: relative;
                }

                .addendum-image-overlay {
                    position: absolute;
                    bottom: 5%;
                    right: 5%;
                    width: 20%; /* Takes up 1/5 of the display area */
                    z-index: 10;
                    border: 2px solid #fff;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const productImageWrapper = document.querySelector('.woocommerce-product-gallery');
                    if (productImageWrapper) {
                        productImageWrapper.classList.add('product-image-wrapper');
                    }
                });
            </script>
            <img src="<?php echo $addendum_image_url; ?>" alt="Addendum Image" class="addendum-image-overlay">
            <?php
        }
    }
});

add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;

    // Check if the current product has an addendum post
    $addendum_post = get_posts([
        'post_type'   => 'addendum',
        'post_parent' => $product->get_id(),
        'numberposts' => 1,
    ]);

    if (!empty($addendum_post)) {
        $addendum_image_url = esc_url($addendum_post[0]->post_content);

        if ($addendum_image_url) {
            ?>
            <style>
                .product-list-image-wrapper {
                    position: relative; /* Ensure the parent container is positioned */
                }

                .product-list-image-wrapper img {
                    display: block; /* Ensure the main product image is displayed properly */
                }

                .addendum-image-overlay {
                    position: absolute;
                    bottom: 5%; /* Position at the lower right corner */
                    right: 5%;
                    width: 20%; /* Takes up 1/5 of the display area */
                    z-index: 10;
                    border: 2px solid #fff;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const productImageWrappers = document.querySelectorAll('.woocommerce-LoopProduct-link img');
                    productImageWrappers.forEach(function (image) {
                        const wrapper = image.closest('.product');
                        if (wrapper) {
                            wrapper.classList.add('product-list-image-wrapper');
                        }
                    });
                });
            </script>
            <img src="<?php echo $addendum_image_url; ?>" alt="Addendum Image" class="addendum-image-overlay">
            <?php
        }
    }
});
