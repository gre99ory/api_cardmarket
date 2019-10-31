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
    add_action( 'wp_ajax_mkm_api_delete_key', 'mkm_api_ajax_delete_key' );
    add_action( 'admin_enqueue_scripts', 'mkm_api_enqueue_admin' );

    if ( !function_exists( 'dump' ) ) {
		function dump( $var ) {
			echo '<pre style="color: #c3c3c3; background-color: #282923;">';
			print_r( $var );
			echo '</pre>';
		}
    }

    function mkm_api_enqueue_admin() {
        wp_enqueue_script( 'mkm-api-admin', plugins_url( 'js/admin_scripts.js', __FILE__ ) );
        wp_enqueue_style( 'mkm-api-admin', plugins_url( 'css/admin_style.css', __FILE__ ) );
    }

    function mkm_api_create_table() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `mkm_api_orders` (
            `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` INT(10) NOT NULL,
            `states` VARCHAR(50) NOT NULL,
            `date_bought` VARCHAR(50) NOT NULL,
            `date_paid` VARCHAR(50) NOT NULL,
            `date_sent` VARCHAR(50) NOT NULL,
            `date_received` VARCHAR(50) NOT NULL,
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
            PRIMARY KEY (`id`)) ENGINE = InnoDBDEFAULT CHARSET=utf8;";

        $wpdb->query($query);
    }

    function mkm_api_ajax_delete_key() {
        $post    = $_POST;

        $flag    = 0;
        $options = get_option( 'mkm_api_options' );

        if ( is_array ( $options ) && count( $options ) > 0 ) {
            $arr = array();
            foreach( $options as $item ) {
                if ( $item['app_token'] == $post['data'] ) continue;
                $arr[] = $item;
            }
        }

        $up = update_option( 'mkm_api_options', $arr );

        if ( $up ) $flag = 1;

        echo $flag;
        die;
    }

    function mkm_api_admin_menu() {
        add_menu_page( 'MKM API', 'MKM API', 'manage_options', 'mkm-api-options', 'mkm_api_options', 'dashicons-groups' );

        add_submenu_page( 'mkm-api-options', 'MKM API DATA', 'API data', 'manage_options', 'mkm-api-subpage', 'mkm_api_data' );
    }

    function mkm_api_admin_settings() {

        register_setting( 'mkm_api_group_options', 'mkm_api_options', 'mkm_api_sanitize' );

    }

    function mkm_api_sanitize( $option ) {

        if ( isset( $_POST['data'] ) ) return $option;

        $add_array             = array();
        $select                = array( 'min', 'hours', 'days' );
        $arr                   = ( is_array( get_option( 'mkm_api_options' ) ) && count( get_option( 'mkm_api_options' ) ) > 0 ) ? get_option( 'mkm_api_options' ) : array();

        if ( $option['name'] == '' ) return $arr;
        if ( $option['app_token'] == '' ) return $arr;
        if ( $option['app_secret'] == '' ) return $arr;
        if ( $option['access_token'] == '' ) return $arr;
        if ( $option['token_secret'] == '' ) return $arr;

        $add_array['name']         = $option['name'];
        $add_array['app_token']    = $option['app_token'];
        $add_array['app_secret']   = $option['app_secret'];
        $add_array['access_token'] = $option['access_token'];
        $add_array['token_secret'] = $option['token_secret'];
        $add_array['interval']     = (int)$option['key'] == 0 ? 1 : $option['key'];
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
                                <table class="mkm-api-apps-show">
                                    <?php foreach( $option as $item ){ ?>
                                    <?php $interval = $item['interval'] . ' ' . $time[$item['time']]; ?>
                                        <tr class="mkm-api-key-row">
                                            <td><?php echo $item['name']; ?></td>
                                            <td>(<?php _e( 'Interval', 'mkm-api' ); ?> : <?php echo $interval; ?>)</td>
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
                                    <label for="mkm_api_setting_interval_id"><?php _e( 'Interval', 'mkm-api' ); ?></label>
                                    <input type="number" value="" class="small-text" name="mkm_api_options[interval]" id="mkm_api_setting_interval_id" >
                                    <label for="mkm_api_setting_time_id"><?php _e( 'Time', 'mkm-api' ); ?></label>
                                    <select name="mkm_api_options[time]" id="mkm_api_setting_time_id">
                                    <?php foreach ( $time as $time_key => $time_val ) { ?>
                                        <option value="<?php echo $time_key; ?>"><?php echo $time_val; ?></option>
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

                <?php submit_button( __( 'Add API', 'mkm-api' ) ); ?>
                </form>
                <?php mkm_api_data(); ?>
            </div>
        <?php
    }

    function mkm_api_data() {

        // $option = get_option( 'mkm_api_options' );
        // if ( isset( $option ) && count( $option ) > 0 ) {
        //     $data   = mkm_api_auth( "https://api.cardmarket.com/ws/v1.1/account", $option[0]['app_token'], $option[0]['app_secret'], $option[0]['access_token'], $option[0]['token_secret'] );
        //     dump($data);

        global $wpdb;
        $option = get_option( 'mkm_api_options' );
        if ( isset( $option ) && count( $option ) > 0 ) {
            $data   = mkm_api_auth( "https://api.cardmarket.com/ws/v2.0/orders/1/8/100", $option[0]['app_token'], $option[0]['app_secret'], $option[0]['access_token'], $option[0]['token_secret'] );

            foreach ( $data->order as $value ) {
                $idOrder         = esc_sql( (int)$value->idOrder );
                $state           = esc_sql( $value->state->state );
                $dateBought      = esc_sql( $value->state->dateBought);
                $datePaid        = esc_sql( $value->state->datePaid );
                $dateSent        = esc_sql( $value->state->dateSent );
                $dateReceived    = esc_sql( $value->state->dateReceived );
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

                if (!$wpdb->get_var( "SELECT id_order FROM mkm_api_orders WHERE id_order = $idOrder" ) ){
                    $wpdb->query($wpdb->prepare("INSERT INTO mkm_api_orders (id_order, states, date_bought, date_paid, date_sent, date_received, price, is_insured, city, country, article_count, evaluation_grade, item_description, packaging, article_value, total_value ) VALUES ( %d, %s, %s, %s, %s, %s, %f, %d, %s, %s, %d, %s, %s, %s, %f, %f )", $idOrder, $state, $dateBought, $datePaid, $dateSent, $dateReceived, $price, $isInsured, $city, $country, $articleCount, $evaluationGrade, $itemDescription, $packaging, $articleValue, $totalValue ) );
                }
            }

        }
    }

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

        // $decoded            = json_decode($content);

        $decoded            = simplexml_load_string($content);

        return $decoded;
    }