<?php
/**
 * Plugin Name: Church Notification Plugin
 * Description: Allows users to add a customizable notification banner.
 * Version: 1.0
 * Author: myChurch Creative
 * Author URI: https://mychurchcreative.com
 */

// Enqueue scripts and styles
function notification_plugin_enqueue_scripts() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('notification-plugin-script', plugins_url('script.js', __FILE__), array('jquery', 'wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'notification_plugin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'notification_plugin_enqueue_scripts'); // Add this line for front-end scripts

// Display the notification banner on the front end
function notification_plugin_display_banner() {
    $notification_text = get_option('notification_text');
    $notification_font_color = get_option('notification_font_color');
    $notification_background_color = get_option('notification_background_color');
    $notification_sticky_position = get_option('notification_sticky_position');

    // Check if the notification banner has been dismissed
    if (!empty($notification_text) && !isset($_COOKIE['notification_dismissed'])) {
        echo '<div class="notification-banner" style="color:' . esc_attr($notification_font_color) . '; background-color:' . esc_attr($notification_background_color) . '; position: sticky; ' . ($notification_sticky_position === 'top' ? 'top' : 'bottom') . ': 0;">';
        echo esc_html($notification_text);
        echo '<a href="#" class="notification-dismiss-link">Dismiss</a>';
        echo '</div>';
    }
}
add_action('wp_footer', 'notification_plugin_display_banner', 100);

// AJAX callback to dismiss the notification banner
function dismiss_notification_banner() {
    setcookie('notification_dismissed', '1', time() + (365 * 24 * 60 * 60), '/');
    wp_die();
}
add_action('wp_ajax_dismiss_notification_banner', 'dismiss_notification_banner');
add_action('wp_ajax_nopriv_dismiss_notification_banner', 'dismiss_notification_banner');


// Add a settings page under the Settings tab in the dashboard
function notification_plugin_add_settings_page() {
    add_options_page(
        'Church Notification Settings',
        'Church Notification',
        'manage_options',
        'notification-plugin-settings',
        'notification_plugin_render_settings_page'
    );
}
add_action('admin_menu', 'notification_plugin_add_settings_page');

// Render the settings page
function notification_plugin_render_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('notification_plugin_settings');
            do_settings_sections('notification-plugin-settings');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Register plugin settings
function notification_plugin_register_settings() {
    add_settings_section(
        'notification_plugin_general_section',
        'General Settings',
        'notification_plugin_general_section_callback',
        'notification-plugin-settings'
    );

    add_settings_field(
        'notification_text',
        'Notification Text',
        'notification_text_field_callback',
        'notification-plugin-settings',
        'notification_plugin_general_section'
    );

    add_settings_field(
        'notification_font_color',
        'Font Color',
        'notification_font_color_field_callback',
        'notification-plugin-settings',
        'notification_plugin_general_section'
    );

    add_settings_field(
        'notification_background_color',
        'Background Color',
        'notification_background_color_field_callback',
        'notification-plugin-settings',
        'notification_plugin_general_section'
    );

    add_settings_field(
        'notification_sticky_position',
        'Sticky Position',
        'notification_sticky_position_field_callback',
        'notification-plugin-settings',
        'notification_plugin_general_section'
    );

    register_setting(
        'notification_plugin_settings',
        'notification_text',
        'sanitize_text_field'
    );

    register_setting(
        'notification_plugin_settings',
        'notification_font_color',
        'sanitize_hex_color'
    );

    register_setting(
        'notification_plugin_settings',
        'notification_background_color',
        'sanitize_hex_color'
    );

    register_setting(
        'notification_plugin_settings',
        'notification_sticky_position',
        'sanitize_text_field'
    );
}
add_action('admin_init', 'notification_plugin_register_settings');

// Callback functions for each setting
function notification_plugin_general_section_callback() {
    echo '<p>Customize the notification banner settings.</p>';
}

function notification_text_field_callback() {
    $notification_text = get_option('notification_text');
    echo '<textarea name="notification_text" rows="4" cols="50">' . esc_textarea($notification_text) . '</textarea>';
}

function notification_font_color_field_callback() {
    $notification_font_color = get_option('notification_font_color');
    echo '<input type="text" class="color-picker" name="notification_font_color" value="' . esc_attr($notification_font_color) . '">';
}

function notification_background_color_field_callback() {
    $notification_background_color = get_option('notification_background_color');
    echo '<input type="text" class="color-picker" name="notification_background_color" value="' . esc_attr($notification_background_color) . '">';
}

function notification_sticky_position_field_callback() {
    $notification_sticky_position = get_option('notification_sticky_position');
    $options = array('top' => 'Top', 'bottom' => 'Bottom');
    echo '<select name="notification_sticky_position">';
    foreach ($options as $value => $label) {
        $selected = selected($value, $notification_sticky_position, false);
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}
