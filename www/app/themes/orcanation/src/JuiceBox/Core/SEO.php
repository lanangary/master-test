<?php
namespace JuiceBox\Core;

class SEO {

    public function __construct() {
        // Create dynamic robots
        add_filter('robots_txt', [$this, 'robots'], 10, 2 );

        // Move 'Yoast' to the bottom of the page
        add_filter('wpseo_metabox_prio', function () {
            return 'low';
        });
    }

    public function robots( $output, $public ) {
        $delim = "\n";

        if(WP_ENV !== 'production'){
            $output = "User-agent: *\nDisallow: /";
            $delim = "\n# ";
        }

        $extras = [
            'Allow: /app/uploads/',
            'Disallow: /app/plugins/',
            'Disallow: /wp/wp-content/plugins/',
            'Disallow: /wp/wp-admin/',
            'Allow: /wp/wp-admin/admin-ajax.php',
            'Disallow: /admin/',
            'Disallow: /administrator/',
            'Disallow: /sitelogin/',
            'Disallow: /readme.html',
            'Disallow: /grid/',
            'Disallow: /typography/',
            'Disallow: /search/',
            'Disallow: /not_found'
        ];

        if(defined('WPSEO_FILE')){
            $extras[] = $delim.sprintf('Sitemap: %s/sitemap_index.xml', WP_HOME);
        }
        return $output.$delim.implode($delim, $extras);
    }

}
?>
