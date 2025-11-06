<?php
/**
 * User Profile Fields for Kibsterlp
 * Adds a Zip Code field to the WordPress user profile page.
 */

namespace Kibsterlp\App\User;

if (!defined('ABSPATH')) exit;

/**
 * Add Zipcode field to user profile
 */
function add_zipcode_field($user) {
    $zipcode = esc_attr(get_the_author_meta('zipcode', $user->ID));
    $is_admin = current_user_can('manage_options');
    ?>

    <h3><?php _e('Extra Profile Information', 'react-admin'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="zipcode"><?php _e('Zip Code', 'react-admin'); ?></label></th>
            <td>
                <?php if ($is_admin) : ?>
                    <input type="text" name="zipcode" id="zipcode"
                           value="<?php echo $zipcode; ?>"
                           class="regular-text" /><br />
                    <span class="description"><?php _e('Admin can enter or edit the user\'s zip code.', 'react-admin'); ?></span>
                <?php else : ?>
                    <input type="text" readonly disabled
                           value="<?php echo $zipcode; ?>"
                           class="regular-text" /><br />
                    <span class="description"><?php _e('This field is managed by the site administrator.', 'react-admin'); ?></span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php }

add_action('show_user_profile', __NAMESPACE__ . '\\add_zipcode_field'); // user’s own profile
add_action('edit_user_profile', __NAMESPACE__ . '\\add_zipcode_field'); // admin editing users

/**
 * Save Zipcode field (admin-only)
 */
function save_zipcode($user_id) {
    // Only admins can change it
    if (!current_user_can('manage_options')) {
        return false;
    }

    if (isset($_POST['zipcode'])) {
        update_user_meta($user_id, 'zipcode', sanitize_text_field($_POST['zipcode']));
    }
}

add_action('personal_options_update', __NAMESPACE__ . '\\save_zipcode'); // admin saving their own
add_action('edit_user_profile_update', __NAMESPACE__ . '\\save_zipcode'); // admin saving others
