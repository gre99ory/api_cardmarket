<?php

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

    global $wpdb;

    $query = "DROP TABLE IF EXISTS `mkm_api_orders`";
    $wpdb->query($query);

    delete_option( 'mkm_api_options' );

?>