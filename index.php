<?php
/**
 * Classic Editor
 *
 * Plugin Name: MKM API
 * Plugin URI:  https://wordpress.org
 * Version:     1.0.6
 * Description: The plugin receives data MKM API
 * Author:      Dmitriy Kovalev
 * Author URI:  https://www.upwork.com/freelancers/~014907274b0c121eb9
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Domain Path: /languages
 *
 */

    /**
     * Wordpress hooks, to which the functions of the plugin are attached, for proper operation
     */
    register_activation_hook( __FILE__, 'mkm_api_create_table_orders' );
    register_activation_hook( __FILE__, 'mkm_api_create_table_account' );
    register_activation_hook( __FILE__, 'mkm_api_create_table_article' );
    register_activation_hook( __FILE__, 'mkm_api_activation' );
    register_deactivation_hook( __FILE__, 'mkm_api_deactivation' );
    add_action( 'admin_menu', 'mkm_api_admin_menu' );
    add_action( 'admin_init', 'mkm_api_admin_settings' );
    add_action( 'wp_ajax_mkm_api_delete_key', 'mkm_api_ajax_delete_key' );
    add_action( 'wp_ajax_mkm_api_ajax_data', 'mkm_api_ajax_get_data' );
    add_action( 'wp_ajax_mkm_api_change_cron_select', 'mkm_api_ajax_change_cron_select' );
    add_action( 'wp_ajax_mkm_api_ajax_get_orders', 'mkm_api_ajax_get_orders' );
    add_action( 'wp_ajax_mkm_api_ajax_update_orders', 'mkm_api_ajax_update_orders' );
    add_action( 'wp_ajax_mkm_api_checkup', 'mkm_api_ajax_checkup' );
    add_action( 'admin_enqueue_scripts', 'mkm_api_enqueue_admin' );
    add_action( 'admin_print_footer_scripts-toplevel_page_mkm-api-options', 'mkm_api_modal_to_footer' );
    add_filter( 'cron_schedules', 'mkm_api_add_schedules', 20 );

    /**
     * Plugin global variables
     */
    $mkmApiBaseUrl = 'https://api.cardmarket.com/ws/v2.0/orders/1/';
    $mkmApiStates  = array(
        'evaluated' => 'Evaluated',
        'bought'    => 'Bought',
        'paid'      => 'Paid',
        'sent'      => 'Sent',
        'received'  => 'Received',
        'lost'      => 'Lost',
        'cancelled' => 'Cancelled'
    );

    /**
     * @return string
     * Custom screen output function for checking
     */
    if ( !function_exists( 'dump' ) ) {
		function dump( $var ) {
			echo '<pre style="color: #c3c3c3; background-color: #282923;">';
			print_r( $var );
			echo '</pre>';
		}
    }

    /**
     * @return string
     * Replacing an empty date value for display
     */
    function mkm_api_null_date( $date ) {
        if ( $date == '1970-01-01 00:00:00' ) return '---- -- --';

        return $date;
    }

    /**
     * @return void
     * Removing cron jobs when deactivation a plugin
     */
    function mkm_api_deactivation() {
        $options = get_option( 'mkm_api_options' );
        if ( is_array( $options ) && count( $options ) > 0 ) {
            foreach( $options as $key => $value ) {
                if ( wp_next_scheduled( 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) ) ) {
                    wp_clear_scheduled_hook( 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) );
                }
            }
        }
    }

    /**
     * @return void
     * Connecting cron jobs when activating the plugin
     */
    function mkm_api_activation() {
        $options = get_option( 'mkm_api_options' );
        if ( is_array( $options ) && count( $options ) > 0 ) {
            foreach( $options as $key => $value ) {
                if ( !wp_next_scheduled( 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) ) ) {
                    wp_schedule_event( time(), $value['cron'], 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) );
                }
            }
        } else {
            update_option( 'mkm_api_options', array() );
        }
    }

    /**
     * @return void
     * Displays a modal window for the progress bar.
     */
    function mkm_api_modal_to_footer() {

        ?>
            <div id="content-for-modal">
                <div class="mkm-api-progress-bar">
                    <span class="mkm-api-progress" style="width: 30%"></span>
                    <span class="proc">30%</span>
                </div>
            </div>
        <?php
    }

    /**
     * @return void
     * Connecting CSS and JS files (custom and WP)
     */
    function mkm_api_enqueue_admin() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'mkm-api-admin', plugins_url( 'js/admin_scripts.js', __FILE__ ) );
        wp_enqueue_style( 'jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );
        wp_enqueue_style( 'mkm-api-admin', plugins_url( 'css/admin_style.css', __FILE__ ) );
    }

    /**
     * @return void
     * Creating a data table for orders when activating the plugin
     */
    function mkm_api_create_table_orders() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `mkm_api_orders` (
            `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` INT(10) NOT NULL,
            `states` VARCHAR(50) NOT NULL,
            `date_bought` DATETIME,
            `date_paid` DATETIME,
            `date_sent` DATETIME,
            `date_received` DATETIME,
            `price` VARCHAR(50) NOT NULL,
            `is_insured` BOOLEAN NOT NULL,
            `city` VARCHAR(255) NOT NULL,
            `country` VARCHAR(255) NOT NULL,
            `article_count` INT(5) NOT NULL,
            `evaluation_grade` VARCHAR(255) NOT NULL,
            `item_description` VARCHAR(255) NOT NULL,
            `packaging` VARCHAR(255) NOT NULL,
            `article_value` VARCHAR(255) NOT NULL,
            `total_value` VARCHAR(255) NOT NULL,
            `appname` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`)) CHARSET=utf8;";

        $wpdb->query($query);
    }

    /**
     * @return void
     * Creating a data table for articles when activating the plugin
     */
    function mkm_api_create_table_article() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `mkm_api_articles` (
            `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_article` INT(10) NOT NULL,
            `id_product` INT(10) NOT NULL,
            `states` VARCHAR(50) NOT NULL,
            `appname` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`)) CHARSET=utf8;";

        $wpdb->query($query);
    }

    /**
     * @return void
     * Creating a data table for account when activating the plugin
     */
    function mkm_api_create_table_account() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `mkm_api_accounts` (
            `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
            `key_account` INT(10) NOT NULL,
            `appname` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`)) CHARSET=utf8;";

        $wpdb->query($query);
    }

    /**
     * @param  app|string
     * @return void
     * Removing orders from the database when deleting the application
     */
    function mkm_api_delete_app_orders( $app ) {
        global $wpdb;
        $wpdb->delete( 'mkm_api_orders', array( 'appname' => $app ), array( '%s' ) );
    }

    /**
     * @return void
     * Uninstall an application when a button is clicked
     */
    function mkm_api_ajax_delete_key() {

        $post    = $_POST;

        $flag    = 0;
        $options = get_option( 'mkm_api_options' );

        if ( is_array ( $options ) && count( $options ) > 0 ) {
            $appname = $options[$post['data']]['name'];
            $arr     = array();
            foreach( $options as $item ) {
                if ( $item['app_token'] == $post['data'] ) continue;
                $arr[$item['app_token']] = $item;
            }
        }

        $up = update_option( 'mkm_api_options', $arr );

        if ( $up ) {
            mkm_api_delete_app_orders( $appname );
            wp_clear_scheduled_hook( 'mkm_api_cron_' . $post['data'], array( array( 'key' => $post['data'] ) ) );
            echo 1;
            wp_die();
        };

        die;
    }

    /**
     * @return string
     * We get all the data by API (works in conjunction with AJAX)
     */
    function mkm_api_ajax_get_data() {
        global $mkmApiBaseUrl;
        $post    = $_POST;
        $arr     = array();
        $key     = $post['key'];
        $api     = array( 1, 2, 4, 8, 32, 128 );
        $state   = 0;

        if( $key == '' ) wp_die( 'end' );
        if( $post['state'] > count( $api ) ) wp_die( 'end' );

        $option = get_option( 'mkm_api_options' );

        if ( $post['count'] == 1 ) {
            $count = mkm_api_auth( "https://api.cardmarket.com/ws/v2.0/account", $option[$key]['app_token'], $option[$key]['app_secret'], $option[$key]['access_token'], $option[$key]['token_secret']);
            $arr['count'] = esc_sql( $count->account->sellCount );
        } else {
            $arr['count'] = $post['count'];
        }

        $data = mkm_api_auth( $mkmApiBaseUrl . $api[$post['state']] . "/" . $post['data'], $option[$key]['app_token'], $option[$key]['app_secret'], $option[$key]['access_token'], $option[$key]['token_secret'] );

        if ( isset( $data->order[0]->idOrder ) && $data->order[0]->idOrder != 0 ) {
            mkm_api_add_data_from_db( $data, $key );
            $arr['data'] = $post['data'] + 100;
            $arr['key']  = $key;
            $arr['state']  = $post['state'];
            echo json_encode( $arr );
        } else {
            if ( $post['state'] <= count( $api ) - 1) {
                mkm_api_add_data_from_db( $data, $key );
                $arr['data'] = 1;
                $arr['key']  = $key;
                $arr['state']  = $post['state'] + 1;
                echo json_encode( $arr );
            } else {
                $option[$key]['get_data'] = 1;
                update_option( 'mkm_api_options', $option );
                echo 'end';
                die;
            }
        }

        die;
    }

    /**
     * @return void
     * Change the checkbox for sort update data (works in conjunction with AJAX)
     */
    function mkm_api_ajax_checkup() {
        // $key    = $_POST['key'];
        // $check  = $_POST['check'];
        // $checks = array( 'orders', 'account', 'articles' );
        //$option = get_option( 'mkm_api_options' );

        // if( !(bool)$key || !(bool)$check || !in_array( $check, $checks ) || !array_key_exists( $key, $option ) ) wp_die( 'error' );
        //$option[$key]['checks'][$check] = 3;

        $up = update_option( 'mkm_api_options', 2 );

        if ( $up ) {
            echo 'check'; die;
        } else {
            echo 'non check';die;
        }


    }

    /**
     * @return void
     * Change the interval of operation of the cron (works in conjunction with AJAX)
     */
    function mkm_api_ajax_change_cron_select() {
        $post    = $_POST;
        $arr     = array();
        $key     = $post['key'];

        if( $key == '' ) wp_die( 'error' );

        $option    = get_option( 'mkm_api_options' );
        $schedules = wp_get_schedules();

        if ( !array_key_exists( $post['data'], $schedules ) ) wp_die( 'error' );

        $option[$key]['cron'] = $post['data'];

        if ( wp_next_scheduled( 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) ) ) {
            wp_clear_scheduled_hook( 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) );
        }

        wp_schedule_event( time(), $post['data'], 'mkm_api_cron_' . $key, array( array( 'key' => $key ) ) );
        update_option( 'mkm_api_options', $option );
    }

    /**
     * @return void
     * Updating order data (works in conjunction with AJAX)
     */
    function mkm_api_ajax_update_orders() {

        $post = $_POST;

        $key = $post['key'];
        if ( !isset( $key ) || !(bool)$key ) {
            echo 'done'; die;
        }

        $options = get_option( 'mkm_api_options' );
        if ( !array_key_exists( $key, $options ) || count( $options ) == 0 ) {
            echo 'done'; die;
        }

        global $mkmApiBaseUrl;
        $arr        = array();
        $count      = $post['count'];
        $state      = $post['state'];
        $api        = array( 1, 2, 4, 8 );
        $arr['key'] = $key;

        $data    = mkm_api_auth( $mkmApiBaseUrl . $api[$state] . "/" . $count, $options[$key]['app_token'], $options[$key]['app_secret'], $options[$key]['access_token'], $options[$key]['token_secret'] );
        if ( isset ( $data->order[0]->idOrder ) &&  $data->order[0]->idOrder != 0 ) {
            sleep( 1 );
            mkm_api_add_data_from_db( $data, $key );
            $arr['count'] = $count + 100;
            $arr['state'] = $state;
            if ( $count >= 301 ) {
                echo 'done'; die;
            }
            echo json_encode( $arr ); die;
        } else {
            if ( $state >= 4 ) {
                echo 'done'; die;
            } else {
                $arr['count'] = 1;
                $arr['state'] = $state + 1;
                echo json_encode( $arr ); die;
            }
        }

        echo 'done'; die;

    }

    /**
     * @return void
     * Forming Plugin Pages
     */
    function mkm_api_admin_menu() {
        add_menu_page( 'MKM API', 'MKM API', 'manage_options', 'mkm-api-options', 'mkm_api_options', 'dashicons-groups' );

        add_submenu_page( 'mkm-api-options', 'MKM API DATA', 'API Orders', 'manage_options', 'mkm-api-subpage', 'mkm_api_orders' );
    }

    /**
     * @return void
     * Formation of the main option for applications
     */
    function mkm_api_admin_settings() {

        register_setting( 'mkm_api_group_options', 'mkm_api_options', 'mkm_api_sanitize' );

    }

    /**
     * @param array
     * @return array
     * Checking and saving options when creating an application
     */
    function mkm_api_sanitize( $option ) {

        if ( isset( $_POST['data'] ) ) return $option;

        $add_array  = array();
        $schedules  = wp_get_schedules();
        $arr        = ( is_array( get_option( 'mkm_api_options' ) ) && count( get_option( 'mkm_api_options' ) ) > 0 ) ? get_option( 'mkm_api_options' ) : array();

        if ( $option['name'] == '' ) return $arr;
        if ( $option['app_token'] == '' ) return $arr;
        if ( $option['app_secret'] == '' ) return $arr;
        if ( $option['access_token'] == '' ) return $arr;
        if ( $option['token_secret'] == '' ) return $arr;
        if ( !array_key_exists( $option['cron'], $schedules ) ) return $arr;
        if ( array_key_exists( $option['app_token'], $arr ) ) {
            add_settings_error( 'mkm_api_options', 'mkm_api_options', __( 'This App Token is already in use', 'mkm-api' ), 'error' );
            return $arr;
        }

        if ( count( $arr ) > 0 ) {
            foreach ( $arr as $app_elem ) {
                if ( $app_elem['name'] == $option['name'] ) {
                    add_settings_error( 'mkm_api_options', 'mkm_api_options', __( 'This name already exists', 'mkm-api' ), 'error' );
                    return $arr;
                }
            }
        }

        $add_array['token_secret'] = $option['token_secret'];
        $add_array['access_token'] = $option['access_token'];
        $add_array['app_secret']   = $option['app_secret'];
        $add_array['app_token']    = $option['app_token'];
        $add_array['checks']       = array( 'orders' => 0, 'account' => 0, 'articles' => 0 );
        $add_array['name']         = $option['name'];
        $add_array['cron']         = $option['cron'];
        $add_array['get_data']     = 0;

        $arr[$option['app_token']] = $add_array;

        if ( !wp_next_scheduled( 'mkm_api_cron_' . $option['app_token'] ) ) {
            wp_schedule_event( time(), $option['cron'], 'mkm_api_cron_' . $option['app_token'], array( array( 'key' => $option['app_token'] ) ) );
        }

        add_settings_error( 'mkm_api_options', 'mkm_api_options', __( 'New application added successfully', 'mkm-api' ), 'updated' );

        return $arr;
    }

    /**
     * @return void
     * Data output to plugin settings page
     */
    function mkm_api_options( ) {
        $option    = get_option( 'mkm_api_options' );
        $schedules = wp_get_schedules();

        ?>

            <div class="wrap">
                <h2><?php _e( 'MKM API Settings', 'mkm-api' ); ?></h2>
                <?php settings_errors(); ?>
                <form action="options.php" method="post">
                    <?php settings_fields( 'mkm_api_group_options' ); ?>
                    <table class="form-table">
                    <?php if ( is_array( $option ) && count( $option ) > 0 ) {  ?>
                        <tr>
                            <th></th>
                            <td class="mkm-api-app-td">
                                <table class="mkm-api-apps-show">
                                    <?php foreach( $option as $item ) { ?>
                                    <?php $interval = ''; ?>
                                        <tr class="mkm-api-key-row">
                                            <td><?php echo $item['name']; ?></td>
                                            <td>
                                                <select class="mkm-api-cron-select" data-key="<?php echo $item['app_token']; ?>">
                                                    <?php foreach( $schedules as $sch_key => $sch_val ) { ?>
                                                        <option <?php echo $sch_key == $item['cron'] ? 'selected ' : ''; ?>value="<?php echo $sch_key; ?>"><?php echo $sch_val['display']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div><?php dump($item['checks']); ?>
                                                    <input <?php echo (bool)$item['checks']['orders'] ? ' checked="checked" ' : '' ?> type="checkbox" id="mkm-api-check-order-<?php echo $item['app_token']; ?>" class="mkm-api-checkup" data-check="orders" data-key="<?php echo $item['app_token']; ?>"/>
                                                    <label for="mkm-api-check-order-<?php echo $item['app_token']; ?>"><?php _e( 'Orders', 'mkm-api' ); ?></label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="mkm-api-check-account-<?php echo $item['app_token']; ?>" class="mkm-api-checkup" data-check="account" data-key="<?php echo $item['app_token']; ?>"/>
                                                    <label for="mkm-api-check-account-<?php echo $item['app_token']; ?>"><?php _e( 'Account', 'mkm-api' ); ?></label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="mkm-api-check-articles-<?php echo $item['app_token']; ?>" class="mkm-api-checkup" data-check="articles" data-key="<?php echo $item['app_token']; ?>"/>
                                                    <label for="mkm-api-check-articles-<?php echo $item['app_token']; ?>"><?php _e( 'Articles', 'mkm-api' ); ?></label>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="button button-primary mkm-api-update-orders" data-key="<?php echo $item['app_token']; ?>">
                                                    <?php _e( 'Update', 'mkm-api' ); ?>
                                                    <span class="mkm-api-update-orders-span">
                                                        <span class="dashicons-before dashicons-update"></span>
                                                    </span>
                                                </button>
                                            </td>
                                            <td class="mkm-api-get-all-data-td"><?php echo (bool)$item['get_data'] ? __( 'Data received', 'mkm-api' ) : submit_button( __( 'Get all data', 'mkm-api' ), 'primary mkm-api-get-all-data', 'submit', true, array( 'data-key' => $item['app_token'] ) ) ?></td>
                                            <td class="mkm-api-delete-key"><a href="" data-key="<?php echo $item['app_token']; ?>"><?php _e( 'Delete', 'mkm-api' ); ?></a></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th></th>
                            <td>
                                <p>
                                    <label class="mkm-api-app-form-label" for="mkm_api_setting_name_id"><?php _e( 'Name App', 'mkm-api' ); ?></label>
                                    <input type="text" value="" class="regular-text" name="mkm_api_options[name]" id="mkm_api_setting_name_id" required>
                                    <label for="mkm_api_setting_cron_id"><?php _e( 'Interval', 'mkm-api' ); ?></label>
                                    <select name="mkm_api_options[cron]" id="mkm_api_setting_cron_id">
                                    <?php foreach ( $schedules as $time_key => $time_val ) { ?>
                                        <option value="<?php echo $time_key; ?>"><?php echo $time_val['display']; ?></option>
                                    <?php } ?>
                                    </select>
                                </p>
                                <p>
                                    <label class="mkm-api-app-form-label" for="mkm_api_setting_app_token_id"><?php _e( 'App Token', 'mkm-api' ); ?></label>
                                    <input type="text" value="" class="regular-text" name="mkm_api_options[app_token]" id="mkm_api_setting_app_token_id" required>
                                </p>
                                <p>
                                    <label class="mkm-api-app-form-label" for="mkm_api_setting_app_secret_id"><?php _e( 'App Secret', 'mkm-api' ); ?></label>
                                    <input type="text" value="" class="regular-text" name="mkm_api_options[app_secret]" id="mkm_api_setting_app_secret_id" required>
                                </p>
                                <p>
                                    <label class="mkm-api-app-form-label" for="mkm_api_setting_access_token_id"><?php _e( 'Access Token', 'mkm-api' ); ?></label>
                                    <input type="text" value="" class="regular-text" name="mkm_api_options[access_token]" id="mkm_api_setting_access_token_id" required>
                                </p>
                                <p>
                                    <label class="mkm-api-app-form-label" for="mkm_api_setting_token_secret_id"><?php _e( 'Access Token Secret', 'mkm-api' ); ?></label>
                                    <input type="text" value="" class="regular-text" name="mkm_api_options[token_secret]" id="mkm_api_setting_token_secret_id" required>
                                </p>
                            </td>
                        </tr>
                    </table>

                <?php submit_button( __( 'Add App', 'mkm-api' ) ); ?>
                </form>
            </div>

        <?php
    }

    /**
     * @return void
     * Recording new and updating old orders in the database
     */
    function mkm_api_add_data_from_db( $data, $key ) {
        global $wpdb;
        $option = get_option( 'mkm_api_options' );

        foreach ( $data->order as $value ) {
            $idOrder         = esc_sql( (int)$value->idOrder );
            if ( !isset( $idOrder ) || $idOrder == 0 ) continue;
            $state           = esc_sql( $value->state->state );
            $dateBought      = date( "Y-m-d H:i:s", strtotime( esc_sql( $value->state->dateBought ) ) );
            $datePaid        = date( "Y-m-d H:i:s", strtotime( esc_sql( $value->state->datePaid ) ) );
            $dateSent        = date( "Y-m-d H:i:s", strtotime( esc_sql( $value->state->dateSent ) ) );
            $dateReceived    = date( "Y-m-d H:i:s", strtotime( esc_sql( $value->state->dateReceived ) ) );
            $price           = esc_sql( $value->shippingMethod->price );
            $isInsured       = (int)esc_sql( $value->shippingMethod->isInsured );
            $city            = esc_sql( $value->shippingAddress->city );
            $country         = esc_sql( $value->shippingAddress->country );
            $articleCount    = (int)esc_sql( $value->articleCount );
            $evaluationGrade = esc_sql( $value->evaluation->evaluationGrade );
            $itemDescription = esc_sql( $value->evaluation->itemDescription );
            $packaging       = esc_sql( $value->evaluation->packaging );
            $articleValue    = esc_sql( $value->articleValue );
            $totalValue      = esc_sql( $value->totalValue );
            $appName         = esc_sql( $option[$key]['name'] );


            if ( !$wpdb->get_var( "SELECT id_order FROM mkm_api_orders WHERE id_order = $idOrder" ) ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO mkm_api_orders (id_order, states, date_bought, date_paid, date_sent, date_received, price, is_insured, city, country, article_count, evaluation_grade, item_description, packaging, article_value, total_value, appname ) VALUES ( %d, %s, %s, %s, %s, %s, %f, %d, %s, %s, %d, %s, %s, %s, %f, %f, %s )", $idOrder, $state, $dateBought, $datePaid, $dateSent, $dateReceived, $price, $isInsured, $city, $country, $articleCount, $evaluationGrade, $itemDescription, $packaging, $articleValue, $totalValue, $appName ) );
            } else {
                $wpdb->update( 'mkm_api_orders',
                    array(
                        'states'           => $state,
                        'date_bought'      => $dateBought,
                        'date_paid'        => $datePaid,
                        'date_sent'        => $dateSent,
                        'date_received'    => $dateReceived,
                        'price'            => $price,
                        'is_insured'       => $isInsured,
                        'city'             => $city,
                        'country'          => $country,
                        'article_count'    => $articleCount,
                        'evaluation_grade' => $evaluationGrade,
                        'item_description' => $itemDescription,
                        'packaging'        => $packaging,
                        'article_value'    => $articleValue,
                        'total_value'      => $totalValue,
                    ),
                    array( 'id_order' => $idOrder ),
                    array( '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%f', '%f' ),
                    array( '%d' )
                );
            }
        }
    }

    /**
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     * The function of connecting via API and receiving data in XML Format
     */
    function mkm_api_auth( $url, $appToken, $appSecret, $accessToken, $accessSecret ) {

        /**
        * Declare and assign all needed variables for the request and the header
        *
        * @var $method string Request method
        * @var $url string Full request URI
        * @var $appToken string App token found at the profile page
        * @var $appSecret string App secret found at the profile page
        * @var $accessToken string Access token found at the profile page (or retrieved from the /access request)
        * @var $accessSecret string Access token secret found at the profile page (or retrieved from the /access request)
        * @var $nonce string Custom made unique string, you can use uniqid() for this
        * @var $timestamp string Actual UNIX time stamp, you can use time() for this
        * @var $signatureMethod string Cryptographic hash function used for signing the base string with the signature, always HMAC-SHA1
        * @var version string OAuth version, currently 1.0
        */

        $method             = "GET";
        $nonce              = wp_create_nonce();
        $timestamp          = time();
        $signatureMethod    = "HMAC-SHA1";
        $version            = "1.0";

        /**
            * Gather all parameters that need to be included in the Authorization header and are know yet
            *
            * Attention: If you have query parameters, they MUST also be part of this array!
            *
            * @var $params array|string[] Associative array of all needed authorization header parameters
            */
        $params             = array(
            'realm'                     => $url,
            'oauth_consumer_key'        => $appToken,
            'oauth_token'               => $accessToken,
            'oauth_nonce'               => $nonce,
            'oauth_timestamp'           => $timestamp,
            'oauth_signature_method'    => $signatureMethod,
            'oauth_version'             => $version,
        );

        /**
            * Start composing the base string from the method and request URI
            *
            * Attention: If you have query parameters, don't include them in the URI
            *
            * @var $baseString string Finally the encoded base string for that request, that needs to be signed
            */
        $baseString         = strtoupper($method) . "&";
        $baseString        .= rawurlencode($url) . "&";

        /*
            * Gather, encode, and sort the base string parameters
            */
        $encodedParams      = array();
        foreach ($params as $key => $value)
        {
            if ("realm" != $key)
            {
                $encodedParams[rawurlencode($key)] = rawurlencode($value);
            }
        }
        ksort($encodedParams);

        /*
            * Expand the base string by the encoded parameter=value pairs
            */
        $values             = array();
        foreach ($encodedParams as $key => $value)
        {
            $values[] = $key . "=" . $value;
        }
        $paramsString       = rawurlencode(implode("&", $values));
        $baseString        .= $paramsString;

        /*
            * Create the signingKey
            */
        $signatureKey       = rawurlencode($appSecret) . "&" . rawurlencode($accessSecret);

        /**
            * Create the OAuth signature
            * Attention: Make sure to provide the binary data to the Base64 encoder
            *
            * @var $oAuthSignature string OAuth signature value
            */
        $rawSignature       = hash_hmac("sha1", $baseString, $signatureKey, true);
        $oAuthSignature     = base64_encode($rawSignature);

        /*
            * Include the OAuth signature parameter in the header parameters array
            */
        $params['oauth_signature'] = $oAuthSignature;

        /*
            * Construct the header string
            */
        $header             = "Authorization: OAuth ";
        $headerParams       = array();
        foreach ($params as $key => $value)
        {
            $headerParams[] = $key . "=\"" . $value . "\"";
        }
        $header            .= implode(", ", $headerParams);

        /*
            * Get the cURL handler from the library function
            */
        $curlHandle         = curl_init();

        /*
            * Set the required cURL options to successfully fire a request to MKM's API
            *
            * For more information about cURL options refer to PHP's cURL manual:
            * http://php.net/manual/en/function.curl-setopt.php
            */
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);

        /**
            * Execute the request, retrieve information about the request and response, and close the connection
            *
            * @var $content string Response to the request
            * @var $info array Array with information about the last request on the $curlHandle
            */
        $content            = curl_exec($curlHandle);
        $info               = curl_getinfo($curlHandle);
        curl_close($curlHandle);

        /*
            * Convert the response string into an object
            *
            * If you have chosen XML as response format (which is standard) use simplexml_load_string
            * If you have chosen JSON as response format use json_decode
            *
            * @var $decoded \SimpleXMLElement|\stdClass Converted Object (XML|JSON)
            */

        //$decoded            = json_decode($content);

        $decoded            = simplexml_load_string($content);

        return $decoded;
    }

    /**
     * @return void
     * Forms an output of these orders to the screen
     */
    function mkm_api_ajax_get_orders(){
        $post = $_POST;

        if ( $post['app'] == '' ) {
            echo 'no_data';
            die();
        }

        $start = ( !isset( $post['start'] ) || $post['start'] == 0 || $post['start'] == '' ) ? 0 : $post['start'];
        $from  = $post['from'] != '' ? $post['from'] . ' 00:00:00' : '1970-01-01 00:00:00';
        $to    = $post['to'] != '' ? $post['to'] . ' 23:59:59' : date( 'Y-m-d H:i:s', time() );

        $html = '';

        $data = mkm_api_get_orders( $start, $post['app'], $from, $to, $post['state'] );
        if ( $data['count'] > 0 ) {
            foreach ( $data['result'] as $res_val ) {
                $html .= '<tr class="mkm-api-list-order-row">';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'ID Order', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->id_order. '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'App name', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->appname . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'Date bought', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . mkm_api_null_date( $res_val->date_bought ) . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Date received', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . mkm_api_null_date( $res_val->date_received ) . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'Date Paid', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . mkm_api_null_date( $res_val->date_paid ) . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Date sent', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . mkm_api_null_date( $res_val->date_sent ) . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'State', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->states. '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Price', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . number_format( $res_val->price, 2, '.', '' ) . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'City/Country', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->city . ' ' . $res_val->country . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Article count', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->article_count . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'Article value', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . number_format( $res_val->article_value   , 2, '.', '' ) . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Total value', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . number_format( $res_val->total_value, 2, '.', '' ) . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'Is insured', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->is_insured . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Packaging', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->packaging . '</div></td>';

                $html .= '<td><div class="mkm-api-td-left">' . __( 'Evaluation grade', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->evaluation_grade . '</div>';
                $html .= '<div class="mkm-api-td-left">' . __( 'Item description', 'mkm-api' ) . '</div><div class="mkm-api-td-right">' . $res_val->item_description . '</div></td>';

                $html .= '</tr>';
            }
        }

        $data['html'] = $html;
        $data['start'] = $start + 30;

        echo json_encode( $data );
        die();
    }

    /**
     * @param int
     * @param string
     * @param string
     * @param string
     * @param string
     * @return array
     * Getting orders from the database
     */
    function mkm_api_get_orders( $start = 0, $apps = 'all', $from = '1970-01-01 00:00:00', $to = 0, $state = 'evaluated' ) {
        global $mkmApiStates;
        global $wpdb;
        $perpage = 30;
        $data    = array();
        $where   = "WHERE states = '$state' AND";
        $to      = $to == 0 ? date( 'Y-m-d H:i:s', time() ) : $to;
        $state   = array_key_exists( $state, $mkmApiStates ) ? $state : 'evaluated';
        if ( $apps != 'all' ) {
            $where .= " appname = '$apps' AND";
        }
        $data['count']  = $wpdb->get_var( "SELECT count(*) FROM mkm_api_orders $where date_bought BETWEEN '$from' AND '$to'" );
        $data['result'] = $wpdb->get_results( "SELECT * FROM mkm_api_orders $where date_bought BETWEEN '$from' AND '$to' ORDER BY date_bought DESC LIMIT $start, $perpage" );
        return $data;
    }

    /**
     * @return void
     * Forms the initial output of these orders to the screen
     */
    function mkm_api_orders() {

        $result  = mkm_api_get_orders();
        $data    = $result['result'];
        $options = get_option( 'mkm_api_options' );
        global $mkmApiStates;

        ?>
            <div class="wrap mkm-api-wrap">
                <h2><?php _e( 'MKM API Orders', 'mkm-api' ); ?></h2>
            <div class="mkm-api-filter">
                <div class="mkm-api-filter-count" data-count="<?php echo $result['count']; ?>">
                    <?php echo __( 'Count orders', 'mkm-api' ) . ': <span class="mkm-api-data-count">' . $result['count']; ?></span>
                </div>

                <div class="mkm-api-filter-select-app">
                    <label for="mkm-api-filter-select-app-id"><?php _e( 'Filter App', 'mkm-api' ); ?></label>
                    <select id="mkm-api-filter-select-app-id">
                        <option value="all"><?php _e( 'All Apps', 'mkm-api' ); ?></option>
                        <?php foreach( $options as $item ) { ?>
                            <option value="<?php echo $item['name']; ?>"><?php echo $item['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mkm-api-filter-select-state">
                    <label for="mkm-api-filter-select-state-id"><?php _e( 'Filter State', 'mkm-api' ); ?></label>
                    <select id="mkm-api-filter-select-state-id">
                        <?php foreach( $mkmApiStates as $state_key => $state_val ) { ?>
                            <option value="<?php echo $state_key; ?>"><?php echo $state_val; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mkm-api-filter-date">
                    <div class="mkm-api-filter-date-item">
                        <?php _e( 'Filter Date: ', 'mkm-api' ); ?>
                    </div>
                    <div class="mkm-api-filter-date-item">
                        <label for="mkm-api-filter-date-from"><?php _e( 'From', 'mkm-api' ); ?></label>
                        <input id="mkm-api-filter-date-from">
                    </div>
                    <div class="mkm-api-filter-date-item">
                        <label for="mkm-api-filter-date-to"><?php _e( 'To', 'mkm-api' ); ?></label>
                        <input id="mkm-api-filter-date-to">
                    </div>
                </div>
            </div>
            <table class="form-table mkm-api-orders-table">
                <tr class="mkm-api-list-orders">
                    <td><?php _e( 'ID Order', 'mkm-api' ); ?><br><?php _e( 'App name', 'mkm-api' ); ?></td>
                    <td><?php _e( 'Date bought', 'mkm-api' ); ?><br><?php _e( 'Date received', 'mkm-api' ); ?></td>
                    <td><?php _e( 'Date paid', 'mkm-api' ); ?><br><?php _e( 'Date sent', 'mkm-api' ); ?></td>
                    <td><?php _e( 'State', 'mkm-api' ); ?><br><?php _e( 'Price', 'mkm-api' ); ?></td>
                    <td><?php _e( 'City/Country', 'mkm-api' ); ?><br><?php _e( 'Article count', 'mkm-api' ); ?></td>
                    <td><?php _e( 'Article value', 'mkm-api' ); ?><br><?php _e( 'Total value', 'mkm-api' ); ?></td>
                    <td><?php _e( 'Is insured', 'mkm-api' ); ?><br><?php _e( 'Packaging', 'mkm-api' ); ?></td>
                    <td><?php _e( 'Evaluation grade', 'mkm-api' ); ?><br><?php _e( 'Item description', 'mkm-api' ); ?></td>
                </tr>
                <?php foreach ( $data as $value ) { ?>
                <tr class="mkm-api-list-order-row">
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'ID Order', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->id_order; ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'App name', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->appname; ?></td>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'Date bought', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo mkm_api_null_date( $value->date_bought ); ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Date received', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo mkm_api_null_date( $value->date_received ); ?></div>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'Date paid', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo mkm_api_null_date( $value->date_paid ); ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Date sent', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo mkm_api_null_date( $value->date_sent ); ?></div>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'State', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->states; ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Price', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo number_format( $value->price, 2, '.', '' ); ?></div>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'City/Country', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->city . ' ' . $value->country;  ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Article count', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->article_count; ?></td>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'Article value', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo number_format( $value->article_value, 2, '.', '' ); ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Total value', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo number_format( $value->total_value, 2, '.', '' ); ?></div>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'Is insured', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->is_insured; ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Packaging', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->packaging; ?></div>
                    </td>
                    <td>
                        <div class="mkm-api-td-left"><?php _e( 'Evaluation grade', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->evaluation_grade; ?></div>
                        <div class="mkm-api-td-left"><?php _e( 'Item description', 'mkm-api' ); ?></div>
                        <div class="mkm-api-td-right"><?php echo $value->item_description; ?></div>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <div class="mkm-api-loader">
                <div class="gear"></div> 
            </div>
            <div class="mkm-api-show-more-list-orders">
                <button class="button button-primary" data-start="30"><?php _e( 'Show more', 'mkm-api' ); ?> <span id="mkm-api-show-more"></span></button>
            </div>
        </div>
        <?php
    }

    /**
     * @return array
     * Adding Time Intervals to Standard WP Intervals
     */
    function mkm_api_add_schedules( $schedules ) {
        $schedules['mkm-api-minute'] = array(
            'interval' => 60,
            'display'  => __( 'Every 1 minute', 'mkm-api' ),
        );

        $schedules['mkm-api-ten-minutes'] = array(
            'interval' => 600,
            'display'  => __( 'Every 10 minutes', 'mkm-api' ),
        );

        $schedules['mkm-api-four-hours'] = array(
            'interval' => 4* HOUR_IN_SECONDS,
            'display'  => __( 'Every 4 hours', 'mkm-api' ),
        );

        uasort( $schedules, function( $a, $b ){
            if ( $a['interval'] == $b['interval'] )return 0;
            return $a['interval'] < $b['interval'] ? -1 : 1;
        });
        return $schedules;
    }

    $options = get_option( 'mkm_api_options' );

    if ( is_array( $options ) && count( $options ) > 0 ) {

        foreach ( $options as $options_key => $options_val ) {
            add_action( 'mkm_api_cron_' . $options_key, 'mkm_cron_setup' );
        }
    }

    /**
     * @param array
     * @return void
     * Performing Cron Tasks
     */
    function mkm_cron_setup( $args ) {

        global $mkmApiBaseUrl;
        $options = get_option( 'mkm_api_options' );
        $key     = $args['key'];
        $flag    = true;
        $count   = 1;
        $state   = 0;
        $api     = array( 1, 2, 4, 8 );

        while ( $flag ) {
            $data    = mkm_api_auth( $mkmApiBaseUrl . $api[$state] . "/" . $count, $options[$key]['app_token'], $options[$key]['app_secret'], $options[$key]['access_token'], $options[$key]['token_secret'] );
            if ( isset ( $data->order[0]->idOrder ) &&  $data->order[0]->idOrder != 0 ) {
                sleep( 1 );
                mkm_api_add_data_from_db( $data, $key );
                $count = $count + 100;
                if ( $count >= 501 ) $flag = false;
            } else {
                if ( $state >= 4 ) {
                    $flag = false;
                } else {
                    $count = 1;
                    $state++;
                }
            }
        }

    }






