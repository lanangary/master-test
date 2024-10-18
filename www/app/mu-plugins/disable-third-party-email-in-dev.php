<?php

/*
* Plugin Name: Disable Third Party Email in Development
* Description: Disable Third Party Email in Development
* Author:      Juicebox
* License:     GNU General Public License v3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('ABSPATH') or die('Cheatin&#8217; uh?');

if (defined('WP_ENV') && WP_ENV !== 'production') {
    add_action('init', function () {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        deactivate_plugins([
            'sendgrid-email-delivery-simplified/wpsendgrid.php',
            'gravityformssendgrid/sendgrid.php',
            'wp-mail-smtp/wp_mail_smtp.php',
            'elastic-email-sender/elasticemailsender.php'
        ]);
    }, 5);
}
