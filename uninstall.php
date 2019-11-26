<?php

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

    global $wpdb;

    $query_orders   = "DROP TABLE IF EXISTS `mkm_api_orders`";
    $wpdb->query( $query_orders );

    $query_articles = "DROP TABLE IF EXISTS `mkm_api_articles`";
    $wpdb->query( $query_articles );

    $query_accounts = "DROP TABLE IF EXISTS `mkm_api_accounts`";
    $wpdb->query( $query_accounts );

    delete_option( 'mkm_api_options' );

?>