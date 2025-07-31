<?php
/**
 * File: includes/fields.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author greghacke
 * Function: Handle Player ID field display and validation
 */

defined('ABSPATH') || exit;

// Add Player ID field to user profile
add_action('show_user_profile', 'pid_show_player_id_field');
add_action('edit_user_profile', 'pid_show_player_id_field');

function pid_show_player_id_field($user) {
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    $can_edit = current_user_can(PID_EDIT_CAPABILITY);
    ?>
    <h3><?php _e('Player Information', PID_TEXT_DOMAIN); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="player_id"><?php _e('Player ID', PID_TEXT_DOMAIN); ?></label></th>
            <td>
                <?php if ($can_edit): ?>
                    <input type="text" 
                           name="player_id" 
                           id="player_id" 
                           value="<?php echo esc_attr($player_id); ?>" 
                           class="regular-text" 
                           required />
                    <p class="description"><?php _e('Unique player identifier. Must be unique across all users.', PID_TEXT_DOMAIN); ?></p>
                <?php else: ?>
                    <strong><?php echo esc_html($player_id ?: __('Not set', PID_TEXT_DOMAIN)); ?></strong>
                    <p class="description"><?php _e('Your unique player identifier. Contact an administrator to change.', PID_TEXT_DOMAIN); ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}

// Save Player ID field
add_action('personal_options_update', 'pid_save_player_id_field');
add_action('edit_user_profile_update', 'pid_save_player_id_field');

function pid_save_player_id_field($user_id) {
    // Only admins can edit
    if (!current_user_can(PID_EDIT_CAPABILITY)) {
        return;
    }
    
    // Check if field is set
    if (!isset($_POST['player_id'])) {
        return;
    }
    
    $new_player_id = sanitize_text_field($_POST['player_id']);
    
    // Validate uniqueness
    if (!pid_validate_player_id_unique($new_player_id, $user_id)) {
        add_action('user_profile_update_errors', function($errors) use ($new_player_id) {
            $errors->add('player_id_duplicate', 
                sprintf(__('Player ID "%s" is already in use by another user.', PID_TEXT_DOMAIN), 
                    esc_html($new_player_id)
                )
            );
        });
        return;
    }
    
    // Save the player ID
    update_user_meta($user_id, PID_META_KEY, $new_player_id);
}

// Validate Player ID uniqueness
function pid_validate_player_id_unique($player_id, $exclude_user_id = 0) {
    if (empty($player_id)) {
        return false;
    }
    
    $args = array(
        'meta_key' => PID_META_KEY,
        'meta_value' => $player_id,
        'exclude' => array($exclude_user_id),
        'number' => 1,
        'fields' => 'ID'
    );
    
    $users = get_users($args);
    return empty($users);
}

// Add Player ID to registration form
add_action('register_form', 'pid_registration_form_field');

function pid_registration_form_field() {
    $player_id = isset($_POST['player_id']) ? sanitize_text_field($_POST['player_id']) : '';
    ?>
    <p>
        <label for="player_id"><?php _e('Player ID', PID_TEXT_DOMAIN); ?><br />
        <input type="text" 
               name="player_id" 
               id="player_id" 
               class="input" 
               value="<?php echo esc_attr($player_id); ?>" 
               size="25" 
               required /></label>
    </p>
    <?php
}

// Validate registration
add_filter('registration_errors', 'pid_registration_errors', 10, 3);

function pid_registration_errors($errors, $sanitized_user_login, $user_email) {
    if (empty($_POST['player_id'])) {
        $errors->add('player_id_empty', __('Player ID is required.', PID_TEXT_DOMAIN));
        return $errors;
    }
    
    $player_id = sanitize_text_field($_POST['player_id']);
    
    if (!pid_validate_player_id_unique($player_id)) {
        $errors->add('player_id_duplicate', 
            sprintf(__('Player ID "%s" is already in use.', PID_TEXT_DOMAIN), 
                esc_html($player_id)
            )
        );
    }
    
    return $errors;
}

// Save Player ID on registration
add_action('user_register', 'pid_user_register');

function pid_user_register($user_id) {
    if (!empty($_POST['player_id'])) {
        $player_id = sanitize_text_field($_POST['player_id']);
        update_user_meta($user_id, PID_META_KEY, $player_id);
    }
}

// Add Player ID to wp-oauth attribute mapping
add_filter('wp_oauth_server_user_attributes', 'pid_oauth_attributes', 10, 2);

function pid_oauth_attributes($attributes, $user) {
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    if ($player_id) {
        $attributes['player_id'] = $player_id;
    }
    return $attributes;
}