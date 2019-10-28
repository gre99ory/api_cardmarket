<?php
/**
 * Classic Editor
 *
 * Plugin Name: MKM API
 * Plugin URI:  https://wordpress.org
 * Version:     1.0.0
 * Description: The plugin receives data MKM API
 * Author:      Dmitriy Kovalev
 * Author URI:  https://www.upwork.com/freelancers/~014907274b0c121eb9
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: classic-editor
 * Domain Path: /languages
 *
 */

    register_activation_hook( __FILE__, 'mkm_api_create_table' );
    add_action( 'admin_menu', 'mkm_api_admin_menu' );

function mkm_api_create_table() {
    global $wpdb;
    $query = "CREATE TABLE IF NOT EXISTS `mkm_api_settings` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `api_key` varchar(100) NOT NULL,
        `api_set` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $wpdb->query($query);
}

function mkm_api_admin_menu() {
    add_options_page( 'MKM API', 'MKM API', 'manage_options', 'mkm-api-plugin', 'mkm_api_page' );
}

function mkm_api_page() {
    ?>

        <div class="mkm-api-wrap">
            <h2><?php _e( 'MKM API Setings', 'mkm-api' ); ?></h2>
        </div>
    <?php
}