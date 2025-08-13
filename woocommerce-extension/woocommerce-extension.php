<?php
/**
 * Plugin Name: Simple Welcome Widget
 * Plugin URI: https://example.com
 * Description: A minimal WordPress plugin that adds a simple welcome message widget.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add a simple welcome message to the admin dashboard
 */
function simple_welcome_dashboard_widget() {
    wp_add_dashboard_widget(
        'simple_welcome_widget',
        'Welcome Message',
        'simple_welcome_widget_content'
    );
}
add_action('wp_dashboard_setup', 'simple_welcome_dashboard_widget');

/**
 * Display the content of the welcome widget
 */
function simple_welcome_widget_content() {
    echo '<p>Welcome to your WordPress dashboard! This message is brought to you by the Simple Welcome Widget plugin.</p>';
    echo '<p>Current time: ' . current_time('mysql') . '</p>';
}

/**
 * Add a settings page to the admin menu
 */
function simple_welcome_admin_menu() {
    add_options_page(
        'Simple Welcome Settings',
        'Simple Welcome',
        'manage_options',
        'simple-welcome-settings',
        'simple_welcome_settings_page'
    );
}
add_action('admin_menu', 'simple_welcome_admin_menu');

/**
 * Display the settings page content
 */
function simple_welcome_settings_page() {
    ?>
    <div class="wrap">
        <h1>Simple Welcome Widget Settings</h1>
        <p>This is a minimal WordPress plugin demonstration.</p>
        <p>The plugin adds a welcome widget to your dashboard.</p>
        <h3>Plugin Information:</h3>
        <ul>
            <li><strong>Version:</strong> 1.0.0</li>
            <li><strong>Status:</strong> Active</li>
            <li><strong>Function:</strong> Displays welcome message on dashboard</li>
        </ul>
    </div>
    <?php
}

/**
 * Plugin activation hook
 */
function simple_welcome_activate() {
    // Code to run when plugin is activated
    add_option('simple_welcome_activated', current_time('mysql'));
}
register_activation_hook(__FILE__, 'simple_welcome_activate');

/**
 * Plugin deactivation hook
 */
function simple_welcome_deactivate() {
    // Code to run when plugin is deactivated
    delete_option('simple_welcome_activated');
}
register_deactivation_hook(__FILE__, 'simple_welcome_deactivate');

/**
 * Add a shortcode for frontend display
 */
function simple_welcome_shortcode($atts) {
    $attributes = shortcode_atts(array(
        'message' => 'Hello from Simple Welcome Widget!'
    ), $atts);
    
    return '<div class="simple-welcome-message" style="padding: 10px; background: #f0f0f0; border-left: 4px solid #0073aa;">' 
           . esc_html($attributes['message']) . 
           '</div>';
}
add_shortcode('simple_welcome', 'simple_welcome_shortcode');

?>