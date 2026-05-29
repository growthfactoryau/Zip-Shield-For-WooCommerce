<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Installer
{
    /**
     * Install plugin.
     */
    public static function install(): void
    {
        self::create_tables();

        update_option('zs_version', ZS_VERSION);
    }

    /**
     * Create database tables.
     */
    private static function create_tables(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $rules_table = $wpdb->prefix . 'zs_rules';

        $sql = "CREATE TABLE {$rules_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			rule_type VARCHAR(50) NOT NULL DEFAULT 'allow_only',
			zipcodes LONGTEXT NOT NULL,
			message TEXT NULL,
			enabled TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id)
		) {$charset_collate};";

        dbDelta($sql);

        $products_table = $wpdb->prefix . 'zs_rule_products';

        $sql = "CREATE TABLE {$products_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			rule_id BIGINT UNSIGNED NOT NULL,
			product_id BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY rule_id (rule_id),
			KEY product_id (product_id)
		) {$charset_collate};";

        dbDelta($sql);

        $categories_table = $wpdb->prefix . 'zs_rule_categories';

        $sql = "CREATE TABLE {$categories_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			rule_id BIGINT UNSIGNED NOT NULL,
			category_id BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY rule_id (rule_id),
			KEY category_id (category_id)
		) {$charset_collate};";

        dbDelta($sql);
    }
}
