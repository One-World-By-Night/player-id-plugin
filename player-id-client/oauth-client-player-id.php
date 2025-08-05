<?php
/**
 * Plugin Name: WP OAuth Client - Player ID Integration
 * Description: Captures player_id from WP OAuth SSO server
 * Version: 3.1.0

// Helper function to write to local debug.log
function pid_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, "$timestamp $message\n", FILE_APPEND);
}

// Intercept ALL HTTP responses to catch OAuth data
add_filter('http_response', function($response, $args, $url) {
    // Check if this is an OAuth endpoint
    if (strpos($url, 'oauth') !== false || strpos($url, '/me') !== false) {
        // Parse response body
        $body = wp_remote_retrieve_body($response);
        if ($body) {
            $data = json_decode($body, true);
            if (is_array($data) && isset($data['player_id'])) {
                // Store it globally for immediate use
                $GLOBALS['oauth_player_id_temp'] = $data['player_id'];
                
                // Store in transient with multiple keys
                set_transient('oauth_player_id_latest', $data['player_id'], 300);
                
                if (isset($data['user_email'])) {
                    set_transient('oauth_player_id_' . md5($data['user_email']), $data['player_id'], 300);
                }
                if (isset($data['user_login'])) {
                    set_transient('oauth_player_id_' . md5($data['user_login']), $data['player_id'], 300);
                }
                if (isset($data['ID'])) {
                    set_transient('oauth_player_id_user_' . $data['ID'], $data['player_id'], 300);
                }
            }
        }
    }
    
    return $response;
}, 10, 3);

// When user is registered
add_action('user_register', function($user_id) {
    // Try multiple methods to get player_id
    $player_id = null;
    
    // Method 1: Global variable
    if (isset($GLOBALS['oauth_player_id_temp'])) {
        $player_id = $GLOBALS['oauth_player_id_temp'];
        unset($GLOBALS['oauth_player_id_temp']);
    }
    
    // Method 2: Latest transient
    if (!$player_id) {
        $player_id = get_transient('oauth_player_id_latest');
        if ($player_id) {
            delete_transient('oauth_player_id_latest');
        }
    }
    
    // Method 3: Email-based transient
    if (!$player_id) {
        $user = get_user_by('id', $user_id);
        if ($user) {
            $player_id = get_transient('oauth_player_id_' . md5($user->user_email));
            if ($player_id) {
                delete_transient('oauth_player_id_' . md5($user->user_email));
            }
        }
    }
    
    // Save player_id if found
    if ($player_id) {
        update_user_meta($user_id, 'player_id', $player_id);
        $user = get_user_by('id', $user_id);
        pid_log("New user: {$user->user_login} (ID: $user_id) | Player ID: $player_id");
    }
}, 1);

// When user logs in
add_action('wp_login', function($user_login, $user) {
    // Only process if user doesn't have player_id
    $existing_player_id = get_user_meta($user->ID, 'player_id', true);
    if (!$existing_player_id) {
        $player_id = null;
        
        // Check global
        if (isset($GLOBALS['oauth_player_id_temp'])) {
            $player_id = $GLOBALS['oauth_player_id_temp'];
            unset($GLOBALS['oauth_player_id_temp']);
        }
        
        // Check transients
        if (!$player_id) {
            $player_id = get_transient('oauth_player_id_latest') ?: 
                         get_transient('oauth_player_id_' . md5($user->user_email)) ?:
                         get_transient('oauth_player_id_' . md5($user_login)) ?:
                         get_transient('oauth_player_id_user_' . $user->ID);
        }
        
        if ($player_id) {
            update_user_meta($user->ID, 'player_id', $player_id);
            pid_log("Existing user: {$user->user_login} (ID: {$user->ID}) | Player ID: $player_id");
            
            // Clean up transients
            delete_transient('oauth_player_id_latest');
            delete_transient('oauth_player_id_' . md5($user->user_email));
            delete_transient('oauth_player_id_' . md5($user_login));
            delete_transient('oauth_player_id_user_' . $user->ID);
        }
    }
}, 1, 2);

// Display in user profile
add_action('show_user_profile', 'show_player_id_field');
add_action('edit_user_profile', 'show_player_id_field');

function show_player_id_field($user) {
    $player_id = get_user_meta($user->ID, 'player_id', true);
    ?>
    <h3>Player Information</h3>
    <table class="form-table">
        <tr>
            <th><label>Player ID</label></th>
            <td>
                <input type="text" value="<?php echo esc_attr($player_id); ?>" class="regular-text" readonly />
                <p class="description">From SSO server</p>
            </td>
        </tr>
    </table>
    <?php
}

// Display in admin user list
add_filter('manage_users_columns', function($columns) {
    $columns['player_id'] = 'Player ID';
    return $columns;
});

add_filter('manage_users_custom_column', function($value, $column_name, $user_id) {
    if ('player_id' === $column_name) {
        return get_user_meta($user_id, 'player_id', true) ?: '-';
    }
    return $value;
}, 10, 3);