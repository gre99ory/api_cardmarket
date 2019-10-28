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
        add_menu_page( 'MKM API', 'MKM API', 'manage_options', 'mkm-api-options', 'mkm_api_options', 'dashicons-groups' );

        add_submenu_page( 'mkm-api-options', 'MKM API DATA', 'API data', 'manage_options', 'mkm-api-subpage', 'mkm_api_data' );
    }

    function mkm_api_options( ) {
        ?>

            <div class="wrap">
                <h2><?php _e( 'MKM API Setings', 'mkm-api' ); ?></h2>
                <form action="options.php" method="post">
                
                <?php submit_button( __( 'Add API', 'mkm-api' ) ); ?>
                </form>
            </div>
        <?php
    }

    function mkm_api_data() {

    }