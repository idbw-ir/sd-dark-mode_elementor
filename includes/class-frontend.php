<?php
namespace SD\DarkMode;

if ( ! defined( 'ABSPATH' ) ) exit;

class Frontend {
    public static function init() {
        add_action( 'wp_head', [ __CLASS__, 'output_js_config' ], 1 );
        add_action( 'wp_head', [ __CLASS__, 'output_css_vars' ], 100 );
        add_filter( 'elementor/widget/render_content', [ __CLASS__, 'filter_widget_images' ], 10, 2 );
    }

    public static function output_js_config() {
        $o   = Settings::get();
        $cfg = wp_json_encode( [
            'mode' => $o['mode'],
            'sw'   => (int) $o['allow_switch'],
            'from' => $o['time_from'],
            'to'   => $o['time_to'],
            'ck'   => 'sd_theme',
            'days' => (int) $o['cookie_days'],
        ], JSON_HEX_TAG | JSON_HEX_AMP );

        $js = <<<'JS'
(function(w,d){
var C=__CFG__,mq=w.matchMedia('(prefers-color-scheme: dark)'),t,cur='';
function ck(n){var m=d.cookie.match(new RegExp('(?:^|; )'+n+'=([^;]*)'));return m?decodeURIComponent(m[1]):''}
function mins(s){var p=s.split(':');return p[0]*60+ +p[1]}
function night(){var n=new Date(),m=n.getHours()*60+n.getMinutes(),a=mins(C.from),b=mins(C.to);return a>b?(m>=a||m<b):(m>=a&&m<b)}
function q(){var m=w.location.search.match(/[?&]sd_theme=(dark|light)/);return m?m[1]:''}
function choice(){if(!C.sw)return '';var v=ck(C.ck);return v==='dark'||v==='light'?v:''}
function resolve(){var v=q()||choice();if(v)return v;if(C.mode==='dark'||C.mode==='light')return C.mode;if(C.mode==='time')return night()?'dark':'light';return mq.matches?'dark':'light'}
function schedule(){clearTimeout(t);if(C.mode!=='time'||choice()||q())return;var n=new Date(),m=n.getHours()*60+n.getMinutes()+n.getSeconds()/60,r=[mins(C.from),mins(C.to)].map(function(x){var f=x-m;return f>0?f:f+1440});t=setTimeout(apply,Math.min(Math.min.apply(null,r)*60000+500,2147000000))}
function apply(){var th=resolve();if(d.body)d.body.classList.toggle('dark',th==='dark');if(th!==cur){cur=th;d.documentElement.setAttribute('data-sd-theme',th);w.dispatchEvent(new CustomEvent('sdthemechange',{detail:{theme:th,choice:choice()||'auto'}}))}schedule()}
w.sdTheme={get:function(){return cur},choice:function(){return choice()||'auto'},
set:function(v){if(!C.sw)return;var e='; path=/; SameSite=Lax'+(w.location.protocol==='https:'?'; Secure':'');if(v==='dark'||v==='light'){d.cookie=C.ck+'='+v+'; max-age='+C.days*86400+e}else{d.cookie=C.ck+'=; max-age=0'+e}apply()},
toggle:function(){this.set(cur==='dark'?'light':'dark')}};
apply();
if(!d.body){var ob=new MutationObserver(function(){if(d.body){apply();ob.disconnect()}});ob.observe(d.documentElement,{childList:true})}
d.addEventListener('DOMContentLoaded',apply);
w.addEventListener('pageshow',apply);
d.addEventListener('visibilitychange',function(){if(!d.hidden)apply()});
if(mq.addEventListener)mq.addEventListener('change',apply);else if(mq.addListener)mq.addListener(apply);
})(window,document);
JS;

        echo '<script data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false" data-nowprocket>'
            . str_replace( '__CFG__', $cfg, $js ) . '</script>';
        echo '<style id="sd-dark-base">body{color-scheme:light}body.dark{color-scheme:dark}body:not(.dark) img.sd-img-dark,body.dark img.sd-img-light{display:none!important}</style>';
    }

    public static function output_css_vars() {
        $css = (string) get_option( Settings::CSS_OPTION, '' );
        $o   = Settings::get();

        if ( ! empty( $o['kit_dark'] ) ) {
            $kit_id = (int) get_option( 'elementor_active_kit' );
            if ( $kit_id ) {
                $k = '';
                foreach ( $o['kit_dark'] as $id => $c ) {
                    $k .= '--e-global-color-' . $id . ':' . $c . ';';
                }
                $css .= 'body.dark.elementor-kit-' . $kit_id . '{' . $k . '}';
            }
        }
        if ( $css ) {
            echo '<style id="sd-dark-vars">' . wp_strip_all_tags( $css ) . '</style>';
        }
    }

    public static function filter_widget_images( $content, $widget ) {
        if ( ! Settings::get( 'widget_images' ) || stripos( $content, '<img' ) === false ) {
            return $content;
        }

        $raw = $widget->get_data( 'settings' );
        if ( ! is_array( $raw ) ) return $content;

        $has = false;
        foreach ( $raw as $k => $v ) {
            if ( is_array( $v ) && substr( $k, -8 ) === '_sd_dark' && ( ! empty( $v['id'] ) || ! empty( $v['url'] ) ) ) { 
                $has = true; break; 
            }
        }
        if ( ! $has && ! empty( $raw['__dynamic__'] ) && is_array( $raw['__dynamic__'] ) ) {
            foreach ( $raw['__dynamic__'] as $k => $v ) {
                if ( substr( $k, -8 ) === '_sd_dark' ) { $has = true; break; }
            }
        }
        if ( ! $has ) return $content;

        $settings = $widget->get_settings_for_display();

        foreach ( $settings as $key => $dark ) {
            if ( substr( $key, -8 ) !== '_sd_dark' || ! is_array( $dark ) ) continue;
            if ( empty( $dark['id'] ) && empty( $dark['url'] ) ) continue;

            $base    = substr( $key, 0, -8 );
            $control = $widget->get_controls( $base );
            if ( ! $control || ! empty( $control['selectors'] ) ) continue;

            $light     = $settings[ $base ] ?? [];
            $light_id  = (int) ( $light['id'] ?? 0 );
            $light_url = $light['url'] ?? '';
            $dark_id   = (int) ( $dark['id'] ?? 0 );

            $dark_srcset = '';
            if ( $dark_id ) {
                $size = $settings[ $base . '_size' ] ?? 'full';
                if ( $size === 'custom' ) {
                    $dark_url = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $dark_id, $base, $settings );
                } else {
                    $dark_url    = wp_get_attachment_image_url( $dark_id, $size );
                    $dark_srcset = wp_get_attachment_image_srcset( $dark_id, $size );
                }
            } else {
                $dark_url = $dark['url'];
            }
            if ( empty( $dark_url ) ) continue;

            $content = preg_replace_callback( '/<img\b[^>]*>/i', function ( $m ) use ( $light_id, $light_url, $dark_id, $dark_url, $dark_srcset ) {
                $img = $m[0];
                if ( strpos( $img, 'sd-img-' ) !== false ) return $img;

                $match = false;
                if ( $light_id )      $match = (bool) preg_match( '/wp-image-' . $light_id . '(?!\d)/', $img );
                elseif ( $light_url ) $match = strpos( $img, $light_url ) !== false;
                if ( ! $match ) return $img;

                $d = self::replace_attr( $img, 'src', $dark_url );
                $d = $dark_srcset ? self::replace_attr( $d, 'srcset', $dark_srcset ) : self::replace_attr( self::replace_attr( $d, 'srcset' ), 'sizes' );
                $d = self::replace_attr( self::replace_attr( $d, 'fetchpriority' ), 'loading', 'lazy' );
                if ( $light_id && $dark_id ) $d = str_replace( 'wp-image-' . $light_id, 'wp-image-' . $dark_id, $d );
                $d = self::add_class( $d, 'sd-img-dark' );

                return self::add_class( $img, 'sd-img-light' ) . $d;
            }, $content );
        }

        return $content;
    }

    private static function replace_attr( $tag, $name, $value = null ) {
        $tag = preg_replace( '/\s' . preg_quote( $name, '/' ) . '=("|\')[^"\']*\1/i', '', $tag );
        if ( $value === null ) return $tag;
        return preg_replace_callback( '/<img\b/i', function () use ( $name, $value ) {
            return '<img ' . $name . '="' . esc_attr( $value ) . '"';
        }, $tag, 1 );
    }

    private static function add_class( $tag, $cls ) {
        if ( preg_match( '/\sclass=("|\')(.*?)\1/i', $tag, $m ) ) {
            $pos = strpos( $tag, $m[0] );
            return substr_replace( $tag, ' class=' . $m[1] . $m[2] . ' ' . $cls . $m[1], $pos, strlen( $m[0] ) );
        }
        return preg_replace( '/<img\b/i', '<img class="' . esc_attr( $cls ) . '"', $tag, 1 );
    }

    // تابع کمکی برای استفاده در قالب (فراخوانی شده از فایل اصلی)
    public static function render_image_helper( $light_url, $dark_url = '', $attrs = [] ) {
        $a = '';
        foreach ( $attrs as $k => $v ) $a .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
        if ( ! $dark_url ) return '<img src="' . esc_url( $light_url ) . '"' . $a . '>';

        return '<img class="sd-img-light" src="' . esc_url( $light_url ) . '"' . $a . '>'
             . '<img class="sd-img-dark" loading="lazy" src="' . esc_url( $dark_url ) . '"' . $a . '>';
    }
}