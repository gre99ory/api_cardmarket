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
    add_action( 'admin_init', 'mkm_api_admin_settings' );

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

    function mkm_api_admin_settings() {
        //option_group, option_name, sanitize_callback
        register_setting( 'mkm_api_group_options', 'mkm_api_options', 'mkm_api_sanitize' );

    }

    function mkm_api_sanitize( $option ) {

        $add_array             = array();
        $select                = array( 'min', 'hours', 'days' );
        $arr                   = ( is_array( get_option( 'mkm_api_options' ) ) && count( get_option( 'mkm_api_options' ) ) > 0 ) ? get_option( 'mkm_api_options' ) : array();

        if ( $option['key'] == '' ) return $arr;

        $add_array['key']      = $option['key'];
        $add_array['interval'] = (int)$option['key'] == 0 ? 1 : $option['key'];
        if ( in_array( $option['time'], $select ) ) {
            $add_array['time'] = $option['time'];
        } else {
            $add_array['time'] = 'min';
        }

        $arr[] = $add_array;

        return $arr;
    }


    function mkm_api_options( ) {
        $option = get_option( 'mkm_api_options' );
        $time   = array( 'min' => __( 'minutes', 'mkm-api' ), 'hours' => __( 'hours', 'mkm-api' ), 'days' => __( 'days', 'mkm-api' ) );
        ?>

            <div class="wrap">
                <h2><?php _e( 'MKM API Settings', 'mkm-api' ); ?></h2>
                <form action="options.php" method="post">
                    <?php settings_fields( 'mkm_api_group_options' ); ?>
                    <table class="form-table">
                    <?php if ( is_array( $option ) && count( $option ) > 0 ) {  ?>
                        <tr>
                            <th></th>
                            <td>
                                <table>
                                    <?php foreach( $option as $item ){ ?>
                                    <?php $interval = $item['interval'] . ' ' . $time[$item['time']]; ?>
                                        <tr>
                                            <td style="width: 50%;"><span style="font-style: italic;"><?php echo $item['key']; ?></span></td>
                                            <td><span>(<?php _e( 'Interval', 'mkm-api' ); ?> : <?php echo $interval; ?>)</span></td>
                                            <td style="width: 10%;"><a href="" style="color: red;"><?php _e( 'Delete', 'mkm-api' ); ?></a></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th><label for="mkm_api_setting_key_id"><?php _e( 'Key', 'mkm-api' ); ?></label></th>
                            <td>
                                <input type="text" value="" class="regular-text" name="mkm_api_options[key]" id="mkm_api_setting_key_id" >
                                <label for="mkm_api_setting_interval_id"><?php _e( 'Interval', 'mkm-api' ); ?></label>
                                <input type="number" value="" class="small-text" name="mkm_api_options[interval]" id="mkm_api_setting_interval_id" >
                                <label for="mkm_api_setting_time_id"><?php _e( 'Time', 'mkm-api' ); ?></label>
                                <select name="mkm_api_options[time]" id="mkm_api_setting_time_id">
                                <?php foreach ( $time as $time_key => $time_val ) { ?>
                                    <option value="<?php echo $time_key; ?>"><?php echo $time_val; ?></option>
                                <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </table>

                <?php submit_button( __( 'Add API', 'mkm-api' ) ); ?>
                </form>
            </div>
        <?php
    }

    function mkm_api_data() {

    }