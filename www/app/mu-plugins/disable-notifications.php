<?php
/**
 * Plugin Name: Disable update notifications
 * Description: Disable please update notifications
 * Author:      Juicebox
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Hide the "Please update now" notification
function hide_update_notice() {
    remove_action( 'admin_notices', 'update_nag', 3 );
}

if (defined('WP_ENV') && WP_ENV === 'production') {
    add_action('admin_notices', 'hide_update_notice', 1);
}
