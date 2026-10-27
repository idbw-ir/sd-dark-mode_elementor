<?php
/**
 * Plugin Name: دارک مود المنتور پیشرفته
 * Plugin URI: https://example.com/sd-dark-mode
 * Description: افزودن قابلیت دارک مود و لایت مود سراسری و ویجتی به وردپرس و المنتور با پنل تنظیمات اختصاصی.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: sd-dark-mode
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// تعریف ثابت‌های افزونه
define( 'SD_DM_VERSION', '1.0.0' );
define( 'SD_DM_DIR', plugin_dir_path( __FILE__ ) );
define( 'SD_DM_URL', plugin_dir_url( __FILE__ ) );

// بارگذاری فایل‌های کلاس
require_once SD_DM_DIR . 'includes/class-plugin.php';

// راه‌اندازی افزونه
function sd_dark_mode_init() {
    \SD\DarkMode\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'sd_dark_mode_init' );

/**
 * تابع کمکی برای استفاده در قالب‌ها (Theme Helper)
 * استفاده: echo sd_dark_img( 'light.jpg', 'dark.jpg', ['class' => 'my-img'] );
 */
if ( ! function_exists( 'sd_dark_img' ) ) {
    function sd_dark_img( $light_url, $dark_url = '', $attrs = [] ) {
        return \SD\DarkMode\Frontend::render_image_helper( $light_url, $dark_url, $attrs );
    }
}