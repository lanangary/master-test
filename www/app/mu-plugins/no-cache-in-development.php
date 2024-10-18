<?php
/**
 * Plugin Name: WP Rocket | Disable Page Caching In Development
 * Description: Disable WP Rocket’s page cache in development.
 * Author:      Juicebox
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if (defined('WP_ENV') && WP_ENV === 'development') {
    add_filter('do_rocket_generate_caching_files', '__return_false');
}
