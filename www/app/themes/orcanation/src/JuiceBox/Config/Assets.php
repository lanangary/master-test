<?php

namespace JuiceBox\Config;

use Timber\URLHelper;

class Assets
{
    public static $css = [];

    public static $js = [];

    public static $async = [];

    /**
     * En-queue required assets
     *
     * @param  string  $filter   The name of the filter to hook into
     * @param  integer $priority The priority to attach the filter with
     */
    public static function load()
    {
        static::add_css([
            'handle'          => 'jb_styles',
            'src'             => '/dist/css/main.css',
        ]);

        static::add_js([
            'handle'          => 'jb_manifest',
            'footer'          => true,
            'src'             => '/dist/js/manifest.js'
        ]);

        static::add_js([
            'handle'          => 'jb_vendor',
            'footer'          => true,
            'src'             => '/dist/js/vendor.js',
            'deps'            => ['jquery', 'jb_manifest']
        ]);

        static::add_js([
            'handle'          => 'jb_modernizr',
            'src'             => '/dist/js/modernizr.js',
            'footer'          => true,
            'deps'            => ['jb_manifest']
        ]);

        static::add_js([
            'handle'          => 'jb_script',
            'src'             => '/dist/js/bundle.js',
            'footer'          => true,
            'deps'            => ['jquery', 'jb_manifest', 'jb_vendor'],
            'data'            => [
                'name'        => 'themeData',
                'config'      => [
                    'childThemeDir'  => get_stylesheet_directory_uri()
                ]
            ]
        ]);

        add_filter('script_loader_tag', __CLASS__.'::add_async_tag', 10, 3);

        add_action('wp_enqueue_scripts', __CLASS__.'::register_files', 10);
    }

    /**
     * Register CSS
     *
     * @param [arrat] $config
     * @return void
     */
    public static function add_css( $config )
    {
        $defaults = [
            'handle'    => null,
            'src'       => null,
            'deps'      => [],
            'ver'       => false,
            'media'     => 'all'
        ];

        $config = array_merge( $defaults, $config );

        if ( !URLHelper::is_external_content($config['src']) ) {
            // Set location
            $config['src'] = get_stylesheet_directory_uri() . $config['src'];
        }

        $release_path = str_replace('/wp/', '', ABSPATH) . '/release.json';
        if( !URLHelper::is_external_content($config['src']) && !$config['ver'] && file_exists($release_path) ){
            $version = json_decode(file_get_contents($release_path), true);
            if(isset($version['version'])){
                $config['ver'] = $version['version'];

                $ext = pathinfo($config['src'], PATHINFO_EXTENSION);
                $config['src'] = str_replace(".{$ext}", ".{$ext}?{$config['ver']}", $config['src']);
            }
        }

        // If version hasn't been passed in and the src isn't external(google fonts)
        if ( !URLHelper::is_external_content($config['src']) && !$config['ver'] ) {
            $config['ver'] = filemtime( get_stylesheet_directory() . "/{$defaults['src']}" );
        }

        static::$css[] = $config;
    }

    /**
     * Register JS
     *
     * @param [arrat] $config
     * @return void
     */
    public static function add_js( $config )
    {
        $defaults = [
            'handle'    => null,
            'src'       => null,
            'deps'      => [],
            'ver'       => false,
            'footer'    => true,
            'data'      => false,
            'async'     => false
        ];

        $config = array_merge( $defaults, $config );

        // Store async handles for later on
        if ( $config['async'] ) {
            static::$async[] = $config['handle'];
        }

        if ( !URLHelper::is_external_content($config['src']) ) {
            // Set location
            $config['src'] = get_stylesheet_directory_uri() . $config['src'];
        }

        $release_path = str_replace('/wp/', '', ABSPATH) . '/release.json';
        if( !URLHelper::is_external_content($config['src']) && !$config['ver'] && file_exists($release_path) ){
            $version = json_decode(file_get_contents($release_path), true);
            if(isset($version['version'])){
                $config['ver'] = $version['version'];

                $ext = pathinfo($config['src'], PATHINFO_EXTENSION);
                $config['src'] = str_replace(".{$ext}", ".{$ext}?{$config['ver']}", $config['src']);
            }
        }

        // If version hasn't been passed in and the src isn't external(google fonts)
        if ( !$config['ver'] && !URLHelper::is_external_content($defaults['src']) ) {
            $config['ver'] = filemtime( get_stylesheet_directory() . "/{$defaults['src']}" );
        }

        static::$js[] = $config;
    }

    public static function add_async_tag( $tag, $handle, $src )
    {
        if ( !in_array($handle, static::$async) ) {
            return $tag;
        }

        return str_replace( '<script', '<script async', $tag );
    }

    public static function register_files()
    {
        // CSS
        if ( static::$css !== [] ) {
            foreach ( static::$css as $css ) {
                wp_enqueue_style( $css['handle'] , $css['src'], $css['deps'], $css['ver'], $css['media']);
            }
        }

        // JS
        if ( static::$js !== array() ) {
            foreach ( static::$js as $js ) {
                if ( isset($js['test']) && call_user_func($js['test']) === false ) {
                    continue;
                }

                wp_enqueue_script( $js['handle'] , $js['src'], $js['deps'], $js['ver'], $js['footer']);

                if ( $js['data'] ) {
                    wp_localize_script($js['handle'], $js['data']['name'], $js['data']['config']);

                    wp_enqueue_script($js['handle']);
                }
            }
        }
    }
}
