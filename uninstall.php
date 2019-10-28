<?php

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

    global $wpdb;

    $query = "DROP TABLE IF EXISTS `mkm_api_settings`";

    $wpdb->query($query);

?>