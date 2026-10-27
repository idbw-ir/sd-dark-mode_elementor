<?php
namespace SD\DarkMode;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once SD_DM_DIR . 'includes/class-settings.php';
        require_once SD_DM_DIR . 'includes/class-frontend.php';
        require_once SD_DM_DIR . 'includes/class-elementor.php';
    }

    private function init_hooks() {
        // بررسی فعال بودن المنتور
        add_action( 'plugins_loaded', [ $this, 'check_elementor' ] );
        
        // فراخوانی کلاس‌ها
        add_action( 'init', [ Settings::class, 'init' ] );
        add_action( 'init', [ Frontend::class, 'init' ] );
        add_action( 'elementor/init', [ Elementor::class, 'init' ] );
    }

    public function check_elementor() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>افزونه <strong>دارک مود المنتور</strong> نیاز به نصب و فعال‌سازی افزونه المنتور دارد.</p></div>';
            });
            return;
        }
    }
}