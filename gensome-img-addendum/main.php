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
                'Product Addendum',
                'Product Addendum',
                'manage_woocommerce',
                'product-addendum',
                'product_addendum_page_callback'
            );
        });
    } else {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>WooCommerce is not active. Please activate WooCommerce to use the Product Addendum feature.</p></div>';
        });
    }
});

function product_addendum_page_callback()
{
    ?>
    <div class="wrap">
        <h1>Product Addendum</h1>
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
                        <th scope="col" class="manage-column">ID</th>
                        <th scope="col" class="manage-column">Product Name</th>
                        <th scope="col" class="manage-column">SKU</th>
                        <th scope="col" class="manage-column">Price</th>
                        <th scope="col" class="manage-column">Stock Status</th>
                        <th scope="col" class="manage-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                foreach ($products as $product) {
                    ?>
                        <tr>
                            <td><?php echo esc_html($product->get_id()); ?></td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_edit_post_link($product->get_id())); ?>">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($product->get_sku() ?: '-'); ?></td>
                            <td><?php echo wp_kses_post($product->get_price_html()); ?></td>
                            <td><?php echo esc_html($product->get_stock_status()); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>">Edit</a>
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
            <p>No products found.</p>
            <?php
    }
    ?>
    </div>
    <?php
}

function gensome_addendum_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'gensome_img_addendum';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        addendum_text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        KEY product_id (product_id)
    ) $charset_collate;";
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'gensome_addendum_create_table');
