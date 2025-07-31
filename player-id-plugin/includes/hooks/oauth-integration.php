<?php
/**
 * File: includes/hooks/oauth-integration.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author greghacke
 * Function: Register Player ID with wp-oauth mapping
 */

defined('ABSPATH') || exit;

// Add player_id to OAuth /me endpoint response
add_filter('wo_me_resource_return', 'pid_add_to_oauth_response', 10, 2);

function pid_add_to_oauth_response($me_data, $token) {
    // Get user ID from the token
    $user_id = isset($token['user_id']) ? $token['user_id'] : 0;
    
    if ($user_id) {
        $player_id = get_user_meta($user_id, PID_META_KEY, true);
        if ($player_id) {
            $me_data['player_id'] = $player_id;
        }
    }
    
    return $me_data;
}

// Add player_id to user info mapping (for custom mapping if needed)
add_filter('wp_oauth_server_user_info_mapping', 'pid_oauth_user_info_mapping');

function pid_oauth_user_info_mapping($mapping) {
    // This allows admins to map player_id to a different field name if needed
    // They would need to manually add this to the database or via custom code
    if (!isset($mapping['player_id'])) {
        $mapping['player_id'] = ''; // Empty means use default field name
    }
    
    return $mapping;
}

// For OpenID Connect support - add player_id as a claim
add_filter('wo_oidc_user_claims', 'pid_add_oidc_claims', 10, 2);

function pid_add_oidc_claims($claims, $user) {
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    if ($player_id) {
        $claims['player_id'] = $player_id;
    }
    
    return $claims;
}

// Add player_id to JWT token if JWT is being used
add_filter('wo_jwt_user_data', 'pid_add_to_jwt', 10, 2);

function pid_add_to_jwt($data, $user_id) {
    $player_id = get_user_meta($user_id, PID_META_KEY, true);
    if ($player_id) {
        $data['player_id'] = $player_id;
    }
    
    return $data;
}