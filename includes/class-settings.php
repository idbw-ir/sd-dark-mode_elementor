<?php
namespace SD\DarkMode;

use Elementor\Plugin as ElementorPlugin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {
    const OPTION_NAME = 'sd_dark_settings';
    const CSS_OPTION  = 'sd_dark_css';

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    public static function defaults() {
        // اجازه فیلتر شدن مقادیر پیش‌فرض برای توسعه‌دهندگان
        return apply_filters( 'sd_dark_defaults', [
            'mode'          => 'system',
            'allow_switch'  => 1,
            'time_from'     => '19:00',
            'time_to'       => '06:00',
            'cookie_days'   => 365,
            'widget_colors' => 1,
            'widget_images' => 1,
            'kit_dark'      => [],
            'vars'          => [],
            'custom'        => [],
        ]);
    }

    public static function get( $key = null ) {
        static $options = null;
        if ( $options === null ) {
            $options = wp_parse_args( get_option( self::OPTION_NAME, [] ), self::defaults() );
        }
        return $key === null ? $options : ( $options[ $key ] ?? null );
    }

    public static function register_settings() {
        register_setting( 'sd_dark_group', self::OPTION_NAME, [
            'sanitize_callback' => [ __CLASS__, 'sanitize' ]
        ]);
    }

    public static function sanitize( $input ) {
        $defaults = self::defaults();
        $input    = is_array( $input ) ? $input : [];
        $out      = [];

        $out['mode'] = in_array( $input['mode'] ?? '', ['system', 'time', 'light', 'dark'], true ) ? $input['mode'] : $defaults['mode'];
        $out['allow_switch']  = empty( $input['allow_switch'] ) ? 0 : 1;
        $out['widget_colors'] = empty( $input['widget_colors'] ) ? 0 : 1;
        $out['widget_images'] = empty( $input['widget_images'] ) ? 0 : 1;

        foreach ( ['time_from', 'time_to'] as $k ) {
            $out[$k] = preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $input[$k] ?? '' ) ? $input[$k] : $defaults[$k];
        }
        $out['cookie_days'] = max( 1, min( 730, (int) ( $input['cookie_days'] ?? 365 ) ) );

        $out['kit_dark'] = [];
        foreach ( (array) ( $input['kit_dark'] ?? [] ) as $id => $val ) {
            $id = sanitize_key( $id );
            $c  = sanitize_hex_color( $val );
            if ( $id && $c ) $out['kit_dark'][$id] = $c;
        }

        $out['vars'] = [];
        foreach ( array_keys( self::theme_vars() ) as $name ) {
            $l = self::clean_value( $input['vars'][$name]['light'] ?? '' );
            $d = self::clean_value( $input['vars'][$name]['dark'] ?? '' );
            if ( $l !== '' || $d !== '' ) $out['vars'][$name] = ['light' => $l, 'dark' => $d];
        }

        $out['custom'] = [];
        foreach ( array_slice( (array) ( $input['custom'] ?? [] ), 0, 30 ) as $row ) {
            $name = self::clean_name( $row['name'] ?? '' );
            $l    = self::clean_value( $row['light'] ?? '' );
            $d    = self::clean_value( $row['dark'] ?? '' );
            if ( $name && ( $l !== '' || $d !== '' ) ) {
                $out['custom'][] = ['name' => $name, 'light' => $l, 'dark' => $d];
            }
        }

        // تولید CSS
        $light = $dark = '';
        foreach ( $out['vars'] as $n => $v ) {
            if ( $v['light'] !== '' ) $light .= $n . ':' . $v['light'] . ';';
            if ( $v['dark']  !== '' ) $dark  .= $n . ':' . $v['dark'] . ';';
        }
        foreach ( $out['custom'] as $r ) {
            if ( $r['light'] !== '' ) $light .= $r['name'] . ':' . $r['light'] . ';';
            if ( $r['dark']  !== '' ) $dark  .= $r['name'] . ':' . $r['dark'] . ';';
        }
        
        $css = '';
        if ( $light ) $css .= ':root{' . $light . '}';
        if ( $dark )  $css .= 'body.dark{' . $dark . '}';
        update_option( self::CSS_OPTION, $css );

        // پاکسازی کش المنتور
        if ( class_exists( '\Elementor\Plugin' ) && isset( ElementorPlugin::$instance->files_manager ) ) {
            ElementorPlugin::$instance->files_manager->clear_cache();
        }

        return $out;
    }

    private static function clean_value( $v ) {
        $v = trim( wp_strip_all_tags( (string) $v ) );
        if ( $v === '' || strlen( $v ) > 200 ) return '';
        if ( ! preg_match( '/^[#a-zA-Z0-9(),.%\s-]+$/', $v ) ) return '';
        if ( preg_match( '/url\(|expression|import|javascript/i', $v ) ) return '';
        return $v;
    }

    private static function clean_name( $n ) {
        $n = strtolower( trim( (string) $n ) );
        return preg_match( '/^--[a-z0-9_-]{1,60}$/', $n ) ? $n : '';
    }

    public static function theme_vars() {
        return apply_filters( 'sd_dark_theme_vars', [
            '--bg-body'          => 'پس‌زمینه بدنه',
            '--bg-box'           => 'پس‌زمینه باکس‌ها',
            '--bg-icon'          => 'رنگ آیکون‌ها',
            '--bg-white'         => 'متن سفید',
            '--bg-des'           => 'متن توضیحات',
            '--bg-h1'            => 'رنگ H1',
            '--bg-h2'            => 'رنگ H2',
            '--bg-h3'            => 'رنگ H3',
            '--bg-h4'            => 'رنگ H4',
            '--bg-h5'            => 'رنگ H5',
            '--bg-h6'            => 'رنگ H6',
            '--bg-border-normal' => 'حاشیه عادی',
            '--bg-border-hover'  => 'حاشیه هاور',
            '--bg-border-focus'  => 'حاشیه فوکوس',
            '--bg-color-normal'  => 'رنگ عادی',
            '--bg-color-hover'   => 'رنگ هاور',
            '--bg-color-focus'   => 'رنگ فوکوس',
            '--bg-hr'            => 'خط جداکننده',
        ]);
    }

    public static function kit_colors() {
        $kit_id = (int) get_option( 'elementor_active_kit' );
        if ( ! $kit_id ) return [];
        $s = get_post_meta( $kit_id, '_elementor_page_settings', true );
        if ( ! is_array( $s ) ) return [];
        
        $out = [];
        foreach ( ['system_colors', 'custom_colors'] as $g ) {
            foreach ( ( $s[$g] ?? [] ) as $c ) {
                if ( ! empty( $c['_id'] ) ) {
                    $out[ $c['_id'] ] = [
                        'title' => $c['title'] ?? $c['_id'],
                        'color' => $c['color'] ?? ''
                    ];
                }
            }
        }
        return $out;
    }

    public static function add_admin_menu() {
        add_options_page( 'تنظیمات دارک مود', '🌙 دارک مود', 'manage_options', 'sd-dark-mode', [ __CLASS__, 'render_page' ] );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'settings_page_sd-dark-mode' ) return;
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_add_inline_script( 'wp-color-picker', "
        jQuery(function($){
            $('.sd-cp').wpColorPicker();
            var i = $('#sd-custom tbody tr').length;
            $('#sd-add').on('click', function(){
                $('#sd-custom tbody').append($('#sd-row-tpl').html().replace(/__i__/g, i++));
            });
            $(document).on('click', '.sd-del', function(){ $(this).closest('tr').remove(); });
        });" );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $o    = self::get();
        $n    = self::OPTION_NAME;
        $kit  = self::kit_colors();
        $vars = self::theme_vars();
        ?>
        <div class="wrap">
            <h1>🌙 تنظیمات دارک مود</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'sd_dark_group' ); ?>

                <h2>۱) تم بر چه اساسی تعیین شود؟</h2>
                <table class="form-table">
                    <tr>
                        <th>حالت پیش‌فرض</th>
                        <td>
                            <?php
                            $modes = [
                                'system' => 'بر اساس تنظیمات دستگاه کاربر',
                                'time'   => 'بر اساس ساعت روز/شب (ساعت محلی دستگاه کاربر)',
                                'light'  => 'همیشه روشن',
                                'dark'   => 'همیشه تاریک',
                            ];
                            foreach ( $modes as $k => $l ) {
                                printf( '<label style="display:block;margin-bottom:6px"><input type="radio" name="%s[mode]" value="%s" %s> %s</label>',
                                    esc_attr( $n ), esc_attr( $k ), checked( $o['mode'], $k, false ), esc_html( $l ) );
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <th>بازه شب (حالت زمانی)</th>
                        <td>
                            از <input type="time" name="<?php echo esc_attr( $n ); ?>[time_from]" value="<?php echo esc_attr( $o['time_from'] ); ?>">
                            تا <input type="time" name="<?php echo esc_attr( $n ); ?>[time_to]" value="<?php echo esc_attr( $o['time_to'] ); ?>">
                            <p class="description">در این بازه تم تاریک فعال می‌شود.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>انتخاب کاربر</th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[allow_switch]" value="1" <?php checked( $o['allow_switch'], 1 ); ?>>
                                اجازه تغییر تم توسط کاربر</label>
                            <p>مدت نگهداری کوکی: <input type="number" min="1" max="730" style="width:80px" name="<?php echo esc_attr( $n ); ?>[cookie_days]" value="<?php echo esc_attr( $o['cookie_days'] ); ?>"> روز</p>
                        </td>
                    </tr>
                </table>

                <h2>۲) رنگ‌های سراسری المنتور</h2>
                <table class="widefat striped" style="max-width:700px">
                    <thead><tr><th>رنگ</th><th>روشن</th><th>دارک</th></tr></thead>
                    <tbody>
                    <?php if ( ! $kit ): ?>
                        <tr><td colspan="3">رنگ سراسری‌ای در کیت المنتور پیدا نشد.</td></tr>
                    <?php else: foreach ( $kit as $id => $c ): ?>
                        <tr>
                            <td><?php echo esc_html( $c['title'] ); ?></td>
                            <td><span style="display:inline-block;width:28px;height:28px;border:1px solid #ccc;vertical-align:middle;background:<?php echo esc_attr( sanitize_hex_color( $c['color'] ) ?: 'transparent' ); ?>"></span> <code><?php echo esc_html( $c['color'] ); ?></code></td>
                            <td><input type="text" class="sd-cp" name="<?php echo esc_attr( $n ); ?>[kit_dark][<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $o['kit_dark'][$id] ?? '' ); ?>"></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <h2>۳) متغیرهای رنگی قالب</h2>
                <table class="widefat striped" style="max-width:900px">
                    <thead><tr><th>متغیر</th><th>روشن</th><th>دارک</th></tr></thead>
                    <tbody>
                    <?php foreach ( $vars as $name => $label ): ?>
                        <tr>
                            <td><?php echo esc_html( $label ); ?> <code><?php echo esc_html( $name ); ?></code></td>
                            <td><input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>[vars][<?php echo esc_attr( $name ); ?>][light]" value="<?php echo esc_attr( $o['vars'][$name]['light'] ?? '' ); ?>"></td>
                            <td><input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>[vars][<?php echo esc_attr( $name ); ?>][dark]" value="<?php echo esc_attr( $o['vars'][$name]['dark'] ?? '' ); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>متغیرهای دلخواه</h3>
                <table class="widefat striped" id="sd-custom" style="max-width:900px">
                    <thead><tr><th>نام (مثل --my-color)</th><th>روشن</th><th>دارک</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ( $o['custom'] as $i => $r ): ?>
                        <tr>
                            <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $r['name'] ); ?>"></td>
                            <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][<?php echo (int) $i; ?>][light]" value="<?php echo esc_attr( $r['light'] ); ?>"></td>
                            <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][<?php echo (int) $i; ?>][dark]" value="<?php echo esc_attr( $r['dark'] ); ?>"></td>
                            <td><button type="button" class="button sd-del">حذف</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="button" class="button" id="sd-add">+ افزودن متغیر</button></p>
                <script type="text/html" id="sd-row-tpl">
                    <tr>
                        <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][__i__][name]"></td>
                        <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][__i__][light]"></td>
                        <td><input type="text" name="<?php echo esc_attr( $n ); ?>[custom][__i__][dark]"></td>
                        <td><button type="button" class="button sd-del">حذف</button></td>
                    </tr>
                </script>

                <h2>۴) ویجت‌ها و تصاویر</h2>
                <table class="form-table">
                    <tr>
                        <th>رنگ دارک جداگانه در استایل ویجت‌ها</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[widget_colors]" value="1" <?php checked( $o['widget_colors'], 1 ); ?>> فعال</label></td>
                    </tr>
                    <tr>
                        <th>تصویر دارک جداگانه</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[widget_images]" value="1" <?php checked( $o['widget_images'], 1 ); ?>> فعال</label></td>
                    </tr>
                </table>

                <?php submit_button( 'ذخیره تنظیمات' ); ?>
            </form>
        </div>
        <?php
    }
}