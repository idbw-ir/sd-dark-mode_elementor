<?php
namespace SD\DarkMode;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor {
    public static function init() {
        add_action( 'elementor/element/kit/section_global_colors/after_section_end', [ __CLASS__, 'add_kit_dark_colors' ] );
        add_action( 'elementor/element/after_section_end', [ __CLASS__, 'add_widget_dark_controls' ], 10, 3 );
    }

    public static function add_kit_dark_colors( $kit ) {
        $kit->start_controls_section( 'sd_dark_global_colors', [
            'label' => '🌙 رنگ‌های سراسری دارک مود',
            'tab'   => 'global-colors',
        ]);

        foreach ( [
            'primary'   => 'رنگ اصلی (Primary)',
            'secondary' => 'رنگ ثانویه (Secondary)',
            'text'      => 'رنگ متن (Text)',
            'accent'    => 'رنگ تاکیدی (Accent)',
        ] as $id => $label ) {
            $kit->add_control( 'sd_dark_color_' . $id, [
                'label'     => $label . ' - دارک',
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ 'body.dark{{WRAPPER}}' => '--e-global-color-' . $id . ': {{VALUE}};' ],
            ]);
        }

        $options = [];
        foreach ( Settings::kit_colors() as $id => $c ) {
            if ( ! in_array( $id, ['primary', 'secondary', 'text', 'accent'], true ) ) {
                $options[ $id ] = $c['title'];
            }
        }

        if ( $options ) {
            $rep = new Repeater();
            $rep->add_control( 'color_id', [
                'label'   => 'رنگ سراسری',
                'type'    => Controls_Manager::SELECT,
                'options' => $options
            ]);
            $rep->add_control( 'color', [
                'label'     => 'رنگ در دارک مود',
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ 'body.dark{{WRAPPER}}' => '--e-global-color-{{color_id.VALUE}}: {{VALUE}};' ],
            ]);
            $kit->add_control( 'sd_dark_custom_colors', [
                'label'       => 'رنگ‌های سفارشی - دارک',
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $rep->get_controls(),
                'title_field' => '{{{ color_id }}}',
            ]);
        }

        $kit->end_controls_section();
    }

    public static function add_widget_dark_controls( $element, $section_id, $args ) {
        $do_colors = (bool) Settings::get( 'widget_colors' );
        $do_images = (bool) Settings::get( 'widget_images' );
        
        if ( ! $do_colors && ! $do_images ) return;
        if ( strpos( $section_id, 'sd_dark_' ) === 0 ) return;
        if ( $element->get_name() === 'kit' ) return;

        $controls = $element->get_controls();
        if ( empty( $controls[ $section_id ] ) ) return;

        $tab = $controls[ $section_id ]['tab'] ?? '';
        if ( ! in_array( $tab, ['content', 'style', 'advanced'], true ) ) return;

        $items = [];
        foreach ( $controls as $name => $c ) {
            if ( ( $c['section'] ?? '' ) !== $section_id ) continue;
            if ( substr( $name, -8 ) === '_sd_dark' ) continue;

            $type = $c['type'] ?? '';

            if ( $type === Controls_Manager::COLOR && $do_colors ) {
                if ( empty( $c['selectors'] ) || $tab === 'content' ) continue;
                $items[ $name ] = $c;
            } elseif ( $type === Controls_Manager::MEDIA && $do_images ) {
                if ( ! empty( $c['media_types'] ) && ! in_array( 'image', (array) $c['media_types'], true ) ) continue;
                $items[ $name ] = $c;
            }
        }
        if ( ! $items ) return;

        $label = $controls[ $section_id ]['label'] ?? $section_id;

        $element->start_controls_section( 'sd_dark_' . $section_id, [
            'label' => '🌙 دارک مود - ' . $label,
            'tab'   => $tab,
        ]);

        foreach ( $items as $name => $c ) {
            $type = $c['type'];
            $new  = [ 'label' => ( $c['label'] ?? $name ) . ' (دارک)', 'type' => $type ];

            if ( ! empty( $c['selectors'] ) ) {
                $selectors = [];
                foreach ( $c['selectors'] as $selector => $value ) {
                    $parts = array_map( function ( $p ) { return 'body.dark ' . trim( $p ); }, explode( ',', $selector ) );
                    $selectors[ implode( ', ', $parts ) ] = $value;
                }
                $new['selectors'] = $selectors;
            }

            if ( $type === Controls_Manager::MEDIA ) {
                $new['media_types'] = ['image'];
                $new['dynamic']     = ['active' => true];
                if ( empty( $c['selectors'] ) ) {
                    $new['description'] = 'اگر خالی بماند، همان تصویر حالت روشن نمایش داده می‌شود.';
                }
            }

            if ( ! empty( $c['condition'] ) )  $new['condition']  = $c['condition'];
            if ( ! empty( $c['conditions'] ) ) $new['conditions'] = $c['conditions'];

            $element->add_control( $name . '_sd_dark', $new );
        }

        $element->end_controls_section();
    }
}