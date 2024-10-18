<?php

namespace JuiceBox\Components\Gallery;

use Timber;

/**
 * Add custom short codes
 */
class Gallery
{
    protected static $gallery_instance = 0;

    public static function register()
    {
        // Remove WP gallery
        add_shortcode('gallery', __CLASS__ . '::custom_gallery');
    }

    public static function custom_gallery($atts)
    {
        static::$gallery_instance++;

        $atts = shortcode_atts([
            'ids' => '',
        ], $atts);

        $atts['ids'] = explode(',', $atts['ids']);

        $atts['images'] = [];

        foreach ( $atts['ids'] as $id ) {
            $atts['images'][] = new \JuiceBox\Core\Image($id);
        }

        $atts['instance'] = static::$gallery_instance;

        return Timber::compile('Gallery/template.twig', $atts);
    }
}
