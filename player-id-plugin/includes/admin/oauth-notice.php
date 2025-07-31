<?php
/**
 * File: includes/admin/oauth-notice.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: Display OAuth configuration information
 */

defined('ABSPATH') || exit;

// Add admin notice on OAuth Server pages
add_action('admin_notices', 'pid_oauth_config_notice');

function pid_oauth_config_notice() {
    $screen = get_current_screen();
    
    // Only show on OAuth Server mapping page
    if (!$screen || $screen->id !== 'oauth-server_page_wo_server_mapping') {
        return;
    }
    
    ?>
    <div class="notice notice-info">
        <p><strong><?php _e('Player ID Integration', PID_TEXT_DOMAIN); ?></strong></p>
        <p><?php _e('The Player ID field is automatically included in OAuth responses. When a user authenticates via OAuth:', PID_TEXT_DOMAIN); ?></p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php _e('The player_id field will be included in the /oauth/me endpoint response', PID_TEXT_DOMAIN); ?></li>
            <li><?php _e('OpenID Connect requests will include player_id as a claim', PID_TEXT_DOMAIN); ?></li>
            <li><?php _e('JWT tokens (if enabled) will contain the player_id field', PID_TEXT_DOMAIN); ?></li>
        </ul>
        <p><?php _e('No additional configuration is required. The player_id will be sent with the same field name unless custom mapping is applied.', PID_TEXT_DOMAIN); ?></p>
    </div>
    <?php
}

// Add Player ID info to OAuth Server status page
add_action('wo_server_status_page_after', 'pid_oauth_status_info');

function pid_oauth_status_info() {
    ?>
    <h3><?php _e('Player ID Integration Status', PID_TEXT_DOMAIN); ?></h3>
    <table class="form-table">
        <tr>
            <th><?php _e('Player ID Field', PID_TEXT_DOMAIN); ?></th>
            <td>
                <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                <?php _e('Active and integrated with OAuth responses', PID_TEXT_DOMAIN); ?>
            </td>
        </tr>
        <tr>
            <th><?php _e('Users with Player ID', PID_TEXT_DOMAIN); ?></th>
            <td>
                <?php
                global $wpdb;
                $total_users = count_users();
                $users_with_player_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''",
                    PID_META_KEY
                ));
                
                printf(
                    __('%d of %d users (%d%%)', PID_TEXT_DOMAIN),
                    $users_with_player_id,
                    $total_users['total_users'],
                    $total_users['total_users'] > 0 ? round(($users_with_player_id / $total_users['total_users']) * 100) : 0
                );
                ?>
            </td>
        </tr>
    </table>
    <?php
}