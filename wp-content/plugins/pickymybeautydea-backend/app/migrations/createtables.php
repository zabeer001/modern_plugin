<?php

/**
 * Kibsterlp Backend – DB migrations
 * Location: app/migrations/createtables.php
 */

namespace Kibsterlp\App\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class CreateTables
{
    const DB_VERSION = '1.1.1'; // bump version
    const OPTION_KEY = 'kibsterlp_db_version';

    public static function up()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $orders       = $wpdb->prefix . 'kib_orders';
        $shipping     = $wpdb->prefix . 'kib_shipping_addresses';
        $categories   = $wpdb->prefix . 'kib_categories';
        $user_details = $wpdb->prefix . 'user_details';

        // Orders table
        $sql_orders = "CREATE TABLE {$orders} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            vendor_id BIGINT UNSIGNED NULL,
            price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            shipping_id BIGINT UNSIGNED NULL,
            budget DECIMAL(12,2) NULL,
            order_title VARCHAR(255) NULL,
            order_unique_id VARCHAR(191) NULL,
            sharing_status VARCHAR(32) NOT NULL DEFAULT 'not accepted',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            payment_status VARCHAR(32) NOT NULL DEFAULT 'pending',
            category_id BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY order_unique_id (order_unique_id),
            KEY user_id (user_id),
            KEY vendor_id (vendor_id),
            KEY shipping_id (shipping_id),
            KEY category_id (category_id),
            CONSTRAINT fk_orders_category FOREIGN KEY (category_id) REFERENCES {$categories}(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) {$charset_collate};";

        // Shipping table
        $sql_shipping = "CREATE TABLE {$shipping} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(191) NULL,
            phone VARCHAR(64) NULL,
            name VARCHAR(191) NULL,
            country VARCHAR(128) NULL,
            city VARCHAR(128) NULL,
            district VARCHAR(128) NULL,
            zip_code VARCHAR(32) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email)
        ) {$charset_collate};";

        // Categories table
        $sql_categories = "CREATE TABLE {$categories} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(191) NULL,
            description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY title (title)
        ) {$charset_collate};";

        // ✅ User details table (without foreign key for now)
        $sql_user_details = "CREATE TABLE {$user_details} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            phone_no VARCHAR(32) NOT NULL,
            address TEXT NULL,
            zipcode VARCHAR(32) NOT NULL,
            state VARCHAR(128) NULL,
            city VARCHAR(128) NULL,
            country VARCHAR(128) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$charset_collate};";

        // Run dbDelta for basic table creation
        dbDelta($sql_categories);
        dbDelta($sql_orders);
        dbDelta($sql_shipping);

        dbDelta($sql_user_details);

        // ✅ Add the foreign key constraint separately (MySQL syntax-safe)
        $fk_name = 'fk_user_details_user';
        $existing_fk = $wpdb->get_results("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = '{$fk_name}' AND TABLE_NAME = '{$user_details}'");

        if (empty($existing_fk)) {
            $wpdb->query("ALTER TABLE {$user_details} ADD CONSTRAINT {$fk_name} FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE");
        }

        update_option(self::OPTION_KEY, self::DB_VERSION);
    }

    public static function maybe_upgrade()
    {
        $installed = get_option(self::OPTION_KEY);
        if ($installed !== self::DB_VERSION) {
            self::up();
        }
    }

    public static function down()
    {
        global $wpdb;
        $orders       = $wpdb->prefix . 'kib_orders';
        $shipping     = $wpdb->prefix . 'kib_shipping_addresses';
        $categories   = $wpdb->prefix . 'kib_categories';
        $user_details = $wpdb->prefix . 'user_details';

        $wpdb->query("SET FOREIGN_KEY_CHECKS = 0");
        $wpdb->query("DROP TABLE IF EXISTS {$orders}");
        $wpdb->query("DROP TABLE IF EXISTS {$shipping}");
        $wpdb->query("DROP TABLE IF EXISTS {$categories}");
        $wpdb->query("DROP TABLE IF EXISTS {$user_details}");
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 1");

        delete_option(self::OPTION_KEY);
    }
}
