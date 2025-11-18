/**
 * Customizer Live Preview
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    // Site title
    wp.customize('blogname', function(value) {
        value.bind(function(newval) {
            $('.site-title').text(newval);
        });
    });

    // Site description
    wp.customize('blogdescription', function(value) {
        value.bind(function(newval) {
            $('.site-description').text(newval);
        });
    });

    // Primary Color
    wp.customize('caniincasa_primary_color', function(value) {
        value.bind(function(newval) {
            $('body').get(0).style.setProperty('--color-primary', newval);
        });
    });

    // Secondary Color
    wp.customize('caniincasa_secondary_color', function(value) {
        value.bind(function(newval) {
            $('body').get(0).style.setProperty('--color-secondary', newval);
        });
    });

    // Accent Color
    wp.customize('caniincasa_accent_color', function(value) {
        value.bind(function(newval) {
            $('body').get(0).style.setProperty('--color-accent', newval);
        });
    });

    // Container Width
    wp.customize('caniincasa_container_width', function(value) {
        value.bind(function(newval) {
            $('body').get(0).style.setProperty('--container-max-width', newval + 'px');
        });
    });

    // Base Font Size
    wp.customize('caniincasa_base_font_size', function(value) {
        value.bind(function(newval) {
            $('body').css('font-size', newval + 'px');
        });
    });

    // Header text color
    wp.customize('header_textcolor', function(value) {
        value.bind(function(newval) {
            if ('blank' === newval) {
                $('.site-title, .site-description').css({
                    'clip': 'rect(1px, 1px, 1px, 1px)',
                    'position': 'absolute'
                });
            } else {
                $('.site-title, .site-description').css({
                    'clip': 'auto',
                    'position': 'relative'
                });
                $('.site-title a, .site-description').css({
                    'color': newval
                });
            }
        });
    });

})(jQuery);
