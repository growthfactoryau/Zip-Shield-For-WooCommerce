<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}zs_rules");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}zs_rule_products");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}zs_rule_categories");

delete_option('zs_version');
