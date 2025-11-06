<?php
if (!defined('ABSPATH')) exit;

class RoleCreationController {

    /**
     * Add custom roles on plugin activation.
     */
    public static function add_role() {
        // --- Vendor role capabilities ---
        $vendor_caps = [
            'read'                   => true,
            'upload_files'           => false,
            'edit_posts'             => false,
            'delete_posts'           => false,
            'publish_posts'          => false,
            'edit_published_posts'   => false,
            'manage_woocommerce'     => false,
        ];

        // --- Customer role capabilities ---
        $customer_caps = [
            'read'                   => true,
            'upload_files'           => false,
            'edit_posts'             => false,
            'delete_posts'           => false,
            'publish_posts'          => false,
            'edit_published_posts'   => false,
            'manage_woocommerce'     => false,
        ];

        // === Vendor ===
        if (get_role('vendor')) {
            $role = get_role('vendor');
            foreach ($vendor_caps as $cap => $grant) {
                if ($grant) {
                    $role->add_cap($cap);
                } else {
                    $role->remove_cap($cap);
                }
            }
        } else {
            add_role('vendor', 'Vendor', $vendor_caps);
        }

        // === Customer ===
        if (get_role('customer')) {
            $role = get_role('customer');
            foreach ($customer_caps as $cap => $grant) {
                if ($grant) {
                    $role->add_cap($cap);
                } else {
                    $role->remove_cap($cap);
                }
            }
        } else {
            add_role('customer', 'Customer', $customer_caps);
        }
    }

    /**
     * Remove custom roles on plugin deactivation.
     */
    public static function remove_role() {
        // Remove Vendor role if it exists
        if (get_role('vendor')) {
            remove_role('vendor');
        }

        // Remove Customer role if it exists
        if (get_role('customer')) {
            remove_role('customer');
        }
    }
}
