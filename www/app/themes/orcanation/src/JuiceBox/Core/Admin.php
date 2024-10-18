<?php

namespace JuiceBox\Core;

use Timber;
use Twig_SimpleFunction;

class Admin
{
    protected $defaultPlugins = array(
        'acf-swatch/acf-swatch.php',
        'advanced-custom-fields-pro/acf.php',
        'wp-nested-pages/nestedpages.php',
        'wp-offload-s3/amazon-s3-and-cloudfront-pro.php',
        'wordpress-seo/wp-seo.php',
        'ithemes-security-pro/ithemes-security-pro.php',
        'wp-h5bp-htaccess/wp-h5bp-htaccess.php',
        'mail/mail.php',
        'email-log/email-log.php',
        'relevanssi/relevanssi.php',
        'redirection/redirection.php',
        'wp-sync-db/wp-sync-db.php',
        'wordpress-importer/wordpress-importer.php',
        'wp-exporter/index.php',
        'favicon-by-realfavicongenerator/favicon-by-realfavicongenerator.php',
        'wp-security-audit-log/wp-security-audit-log.php',
        'juicy/juicy.php',
        'soil/soil.php',
    );

    public function __construct()
    {
        // customise WYSIWYG
        add_filter('tiny_mce_before_init',      [$this, 'add_styles_to_wysiwyg']);
        add_action('after_setup_theme',         [$this, 'add_wysiwyg_stylesheet']);
        add_filter('mce_buttons_2',             [$this, 'add_mce_buttons_2']);

        // Remove options for clients to deactivate plugins
        add_filter('plugin_action_links',       [$this, 'jb_remove_deactivate'], 10, 4 );

        // Auto activate plugins
        add_action('admin_init',                [$this, 'jb_activate_plugins'] );

        if (function_exists('acf_add_options_page')) {
            $this->options_pages();
        }
    }

    // Automatically activate required plugins.
    public function jb_activate_plugins()
    {
        $current_plugins = get_option('active_plugins'); // get active plugins

        foreach ( $this->defaultPlugins as $plugin ) {
            // If the plugin isnt currently active
            if ( ! in_array( $plugin, $current_plugins ) ) {
                activate_plugin($plugin);
            }
        }
    }

    // Removes the ability for core plugins to be deactivated.
    public function jb_remove_deactivate( $actions, $plugin_file, $plugin_data, $context )
    {
        if ( in_array( $plugin_file, $this->defaultPlugins ) && isset($actions['deactivate']) ) {
            unset($actions['deactivate']);
        }

        return $actions;
    }

    public function options_pages()
    {
        acf_add_options_page(array(
            'page_title'    => 'Theme General Settings',
            'menu_title'    => 'Theme Settings',
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }

    public function add_styles_to_wysiwyg($init_array)
    {
        $init_array['block_formats'] = "Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;";
        $init_array['menubar'] = true;

        $style_formats = array(
            // Each array child is a format with it's own settings
            [
                'title' => 'Large Paragraph',
                'selector' => 'p',
                'classes' => 'lead',
            ],
        );

        // Insert the array, JSON ENCODED, into 'style_formats'
        $init_array['style_formats'] = json_encode( $style_formats );

        return $init_array;
    }

    public function add_mce_buttons_2( $buttons ) {
        array_unshift( $buttons, 'styleselect' );
        return $buttons;
    }

    public function add_wysiwyg_stylesheet()
    {
        if (file_exists(get_stylesheet_directory() . '/dist/css/editor-style.min.css')) {
            add_editor_style("/dist/css/editor-style.min.css");
        } else {
            add_editor_style("/dist/css/editor-style.css");
        }
    }
}
