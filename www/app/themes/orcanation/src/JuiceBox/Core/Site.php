<?php

namespace JuiceBox\Core;

use Timber\Site as TimberSite;
use JuiceBox\Core\Menu;
use JuiceBox\Core\Image;
use JuiceBox\Core\Term;
use JuiceBox\Core\Post;

class Site extends TimberSite
{
    protected $PostClass = Post::class;
    protected $MenuClass = Menu::class;
    protected $ImageClass = Image::class;
    protected $TermClass = Term::class;

    public function __construct()
    {
        //Add global variables to twig
        add_filter('timber_context', array($this, 'add_to_context'));

        //Add custom functions to twig
        add_filter('timber/twig', [$this, 'child_add_to_twig']);

        // clear default wordpress gallery stuff
        add_filter('use_default_gallery_style', '__return_false');

        // Check if post needs a password here, removes it from page.php/single.php
        add_filter('timber_render_file', array($this, 'maybe_load_password_template'));

        // Add responsive wrapper around oEmbed elements and tables
        add_filter('embed_oembed_html', [$this, 'wrap_embed'], 10, 1);

        // Prevent cache blocking
        if (WP_ENV === 'production') {
            add_filter('rocket_override_donotcachepage', '__return_true', PHP_INT_MAX);
        }

        parent::__construct();
    }

    public function add_to_context($context)
    {
        return $context;
    }


    /**
     * If post needs a password load the correct template.
     */
    public function maybe_load_password_template($file)
    {
        global $post;

        if (isset($post->ID) && post_password_required($post->ID)) {
            $file = 'password.twig';
        }

        return $file;
    }

    /**
     * Filter for adding wrappers around oEmbeds
     */
    public function wrap_embed($html)
    {
        $html = preg_replace('/(width|height|frameborder|scrolling)="[a-z0-9]*"\s/i', "", $html); // Strip width, height, frameborder, scrolling #1
        $html = preg_replace('/(webkitallowfullscreen mozallowfullscreen)\s/i', "", $html); // Strip vendor attributes

        return '<div class="embed-responsive">' . $html . '</div>'; // Wrap in div element and return #3 and #4
    }

    /* this is where you can add your own functions to twig */
    public function child_add_to_twig($twig)
    {
        $twig->addFunction(new \Twig\TwigFunction('svg_icon', [$this, 'svg_icon']));

        return $twig;
    }

    public function svg_icon($name = ''){
        $icon_path = get_stylesheet_directory() . '/img/' . $name . '.svg';
        if (file_exists($icon_path)) {
            return file_get_contents($icon_path);
        }
        $icon_path = get_stylesheet_directory() . '/icons/SVG/' . $name . '.svg';
        if (file_exists($icon_path)) {
            return file_get_contents($icon_path);
        }
        return env('WP_ENV') === 'development' ? "Icon Missing" : null;
    }
}
