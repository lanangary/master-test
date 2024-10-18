<?php

namespace JuiceBox\Config;

use JuiceBox\Components\Gallery\Gallery;
use Timber;

/**
 * Add custom short codes
 */
class Shortcodes
{
    public static function register()
    {
        add_shortcode('theme_option', array(__CLASS__, 'theme_option'));
        add_shortcode('html-sitemap', array(__CLASS__, 'html_sitemap'));

        Gallery::register();
    }


    public static function theme_option( $atts )
    {
        $atts = shortcode_atts([
            'value' => ''
        ], $atts);

        if ( $atts['value'] == '' ) {
            return new \WP_Error('Missing Value', 'Please Specify a value for this shortcode.', $atts);
        }

        if ( $atts['value'] == 'client_name' ) {
            $option = get_option('blogname');
        } else {
            $option = get_field($atts['value'], 'option');
        }

        if ( empty($option) ) {
            return new \WP_Error('Empty Field', "The value returned from your specified option (`{$atts['value']}`) is either empty or doesn't exist.", $atts);
        }

        return $option;
    }

    public static function html_sitemap() {
        $sections = array();

        foreach( get_post_types( array('public' => true) ) as $post_type ) {
            if ( in_array( $post_type, array('attachment') ) )
                continue;

            $pt = get_post_type_object( $post_type );

            $posts = Timber::get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'sort_column' => 'menu_order'
            ), "\\JuiceBox\\Core\\Post");

            $filtered_posts = [];

            foreach($posts as $post) {
                $yoast_exclude = get_post_meta($post->id, '_yoast_wpseo_meta-robots-noindex', true); 
                if(!$yoast_exclude) {
                    $filtered_posts[] = $post;
                }
            }

            $sections[] = array(
                'title' => $pt->labels->name,
                'items' => $filtered_posts
            );
        }

        $context = array(
            'sitemap' => $sections
        );

        return Timber::compile('partials/sitemap.twig', $context);
    }

}
