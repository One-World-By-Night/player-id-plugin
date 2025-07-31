<?php
/**
 * File: includes/hooks/oauth-integration.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: Register Player ID with wp-oauth mapping
 */

defined('ABSPATH') || exit;

// Register player_id as a mappable attribute
add_filter('wp_oauth_server_available_user_attributes', 'pid_register_oauth_attribute');

function pid_register_oauth_attribute($attributes) {
    $attributes['player_id'] = array(
        'label' => __('Player ID', PID_TEXT_DOMAIN),
        'type' => 'text',
        'description' => __('Unique player identifier', PID_TEXT_DOMAIN),
    );
    
    return $attributes;
}

// Provide the value for the player_id attribute
add_filter('wp_oauth_server_user_attribute_value', 'pid_oauth_attribute_value', 10, 3);

function pid_oauth_attribute_value($value, $attribute, $user_id) {
    if ($attribute === 'player_id') {
        $value = get_user_meta($user_id, PID_META_KEY, true);
    }
    
    return $value;
}

// Alternative hook if wp-oauth uses different naming
add_filter('wo_get_user_metadata', 'pid_oauth_metadata', 10, 2);

function pid_oauth_metadata($metadata, $user_id) {
    $player_id = get_user_meta($user_id, PID_META_KEY, true);
    if ($player_id) {
        $metadata['player_id'] = $player_id;
    }
    
    return $metadata;
}

// Register claim for OpenID Connect
add_filter('wp_oauth_server_userinfo_claims', 'pid_oauth_userinfo_claims', 10, 2);

function pid_oauth_userinfo_claims($claims, $user) {
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    if ($player_id) {
        $claims['player_id'] = $player_id;
    }
    
    return $claims;
}