<?php
/**
 * Plugin Name: دارک مود المنتور پیشرفته
 * Plugin URI: https://github.com/idbw-ir/sd-dark-mode_elementor
 * Description: افزودن قابلیت دارک مود و لایت مود سراسری و ویجتی به وردپرس و المنتور با پنل تنظیمات اختصاصی.
 * Version: 1.0.1
 * Author: حسین عظیمی بیرجندی
 * Text Domain: sd-dark-mode
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// تعریف ثابت‌های افزونه
define( 'SD_DM_VERSION', '1.0.1' );
define( 'SD_DM_DIR', plugin_dir_path( __FILE__ ) );
define( 'SD_DM_URL', plugin_dir_url( __FILE__ ) );
define( 'SD_DM_BASENAME', plugin_basename( __FILE__ ) );

// بارگذاری فایل‌های کلاس
require_once SD_DM_DIR . 'includes/class-plugin.php';

/**
 * راه‌اندازی افزونه
 */
function sd_dark_mode_init() {
    \SD\DarkMode\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'sd_dark_mode_init' );

/**
 * اضافه کردن لینک تنظیمات در صفحه افزونه‌ها
 */
add_filter( 'plugin_action_links_' . SD_DM_BASENAME, function( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=sd-dark-mode' ) . '">' . __( 'تنظیمات', 'sd-dark-mode' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
});

/**
 * اضافه کردن لینک مستندات/پشتیبانی (اختیاری)
 */
add_filter( 'plugin_row_meta', function( $links, $file ) {
    if ( $file === SD_DM_BASENAME ) {
        $new_links = array(
            '<a href="https://github.com/idbw-ir/sd-dark-mode_elementor">' . __( 'مستندات افزونه', 'sd-dark-mode' ) . '</a>',
        );
        $links = array_merge( $links, $new_links );
    }
    return $links;
}, 10, 2 );

/**
 * تابع کمکی برای استفاده در قالب‌ها (Theme Helper)
 * استفاده: echo sd_dark_img( 'light.jpg', 'dark.jpg', ['class' => 'my-img'] );
 */
if ( ! function_exists( 'sd_dark_img' ) ) {
    function sd_dark_img( $light_url, $dark_url = '', $attrs = [] ) {
        if ( class_exists( '\SD\DarkMode\Frontend' ) ) {
            return \SD\DarkMode\Frontend::render_image_helper( $light_url, $dark_url, $attrs );
        }
        
        // Fallback اگر کلاس لود نشده باشد
        $a = '';
        foreach ( $attrs as $k => $v ) {
            $a .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
        }
        
        if ( ! $dark_url ) {
            return '<img src="' . esc_url( $light_url ) . '"' . $a . '>';
        }

        return '<img class="sd-img-light" src="' . esc_url( $light_url ) . '"' . $a . '>'
             . '<img class="sd-img-dark" loading="lazy" src="' . esc_url( $dark_url ) . '"' . $a . '>';
    }
}

/**
 * هوک فعال‌سازی افزونه
 */
register_activation_hook( __FILE__, function() {
    // اضافه کردن تنظیمات پیش‌فرض
    $defaults = \SD\DarkMode\Settings::defaults();
    add_option( 'sd_dark_settings', $defaults );
    
    // پاکسازی کش
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }
});

/**
 * هوک غیرفعال‌سازی افزونه
 */
register_deactivation_hook( __FILE__, function() {
    // پاکسازی کش
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }
});

/**
 * بررسی آپدیت‌ها (می‌توانید در آینده برای آپدیت خودکار استفاده کنید)
 */
add_action( 'after_plugin_row_' . SD_DM_BASENAME, function( $plugin_file, $plugin_data, $status ) {
    if ( version_compare( SD_DM_VERSION, '1.0.1', '<' ) ) {
        echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>نسخه جدیدی از افزونه موجود است.</p></div></td></tr>';
    }
}, 10, 3 );