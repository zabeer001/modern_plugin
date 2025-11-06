<?php
// /var/www/wordpress/modern_plugin/wp-content/plugins/zabeer-auth/app/class/ExtraInfoController.php

if (!defined('ABSPATH')) {
    exit;
}

class ExtraInfoController
{
    public function __construct()
    {
        // Show fields in user profile (backend)
        add_action('show_user_profile', [$this, 'render_vendor_fields']);
        add_action('edit_user_profile', [$this, 'render_vendor_fields']);

        // Save fields
        add_action('personal_options_update', [$this, 'save_vendor_fields']);
        add_action('edit_user_profile_update', [$this, 'save_vendor_fields']);
    }

    /**
     * Render extra vendor info fields in profile
     */
    public function render_vendor_fields($user)
    {
        if (!in_array('vendor', (array) $user->roles)) {
            return; // only for vendors
        }

        $fields = [
            'vendor_address'      => __('Adresse', 'zabeer-auth'),
            'zip_code'            => __('Postleitzahl', 'zabeer-auth'),
            'vendor_city'         => __('Stadt', 'zabeer-auth'),
            'vendor_phone'        => __('Telefonnummer', 'zabeer-auth'),
            'vendor_description'  => __('Beschreibung', 'zabeer-auth'),
            'vendor_logo_url'     => __('Logo / Bild', 'zabeer-auth'),
        ];
        ?>

        <h2><?php esc_html_e('Vendor Information', 'zabeer-auth'); ?></h2>
        <table class="form-table">
            <?php foreach ($fields as $meta_key => $label): ?>
                <tr>
                    <th><label for="<?php echo esc_attr($meta_key); ?>"><?php echo esc_html($label); ?></label></th>
                    <td>
                        <?php
                        $value = get_user_meta($user->ID, $meta_key, true);

                        // Special case for logo preview
                        if ($meta_key === 'vendor_logo_url' && !empty($value)) {
                            echo '<img src="' . esc_url($value) . '" alt="Logo" style="width:80px;height:80px;border-radius:8px;display:block;margin-bottom:8px;">';
                        }

                        // Input field
                        if ($meta_key === 'vendor_description') {
                            echo '<textarea name="' . esc_attr($meta_key) . '" rows="3" cols="50" class="regular-text">' . esc_textarea($value) . '</textarea>';
                        } else {
                            echo '<input type="text" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php
    }

    /**
     * Save extra vendor info fields
     */
    public function save_vendor_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        $meta_keys = [
            'vendor_address',
            'zip_code',
            'vendor_city',
            'vendor_phone',
            'vendor_description',
            'vendor_logo_url'
        ];

        foreach ($meta_keys as $key) {
            if (isset($_POST[$key])) {
                update_user_meta($user_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }
}


new ExtraInfoController();