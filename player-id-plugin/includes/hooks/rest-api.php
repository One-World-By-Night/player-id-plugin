<?php
/**
 * File: includes/hooks/rest-api.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: REST API endpoints for Player ID validation
 */

defined('ABSPATH') || exit;

// Register REST API routes
add_action('rest_api_init', 'pid_register_rest_routes');

function pid_register_rest_routes() {
    register_rest_route('player-id/v1', '/check', array(
        'methods' => 'GET',
        'callback' => 'pid_check_availability',
        'permission_callback' => '__return_true',
        'args' => array(
            'player_id' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'user_id' => array(
                'required' => false,
                'type' => 'integer',
                'default' => 0,
                'sanitize_callback' => 'absint',
            ),
        ),
    ));
    
    register_rest_route('player-id/v1', '/user/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'pid_get_user_player_id',
        'permission_callback' => 'pid_can_view_player_id',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
}

// Check Player ID availability
function pid_check_availability($request) {
    $player_id = $request->get_param('player_id');
    $user_id = $request->get_param('user_id');
    
    if (empty($player_id)) {
        return new WP_Error('empty_player_id', __('Player ID cannot be empty.', PID_TEXT_DOMAIN), array('status' => 400));
    }
    
    $is_available = pid_validate_player_id_unique($player_id, $user_id);
    
    return array(
        'available' => $is_available,
        'player_id' => $player_id,
        'message' => $is_available 
            ? __('Player ID is available.', PID_TEXT_DOMAIN) 
            : __('Player ID is already in use.', PID_TEXT_DOMAIN),
    );
}

// Get user's Player ID
function pid_get_user_player_id($request) {
    $user_id = $request->get_param('id');
    $user = get_user_by('id', $user_id);
    
    if (!$user) {
        return new WP_Error('user_not_found', __('User not found.', PID_TEXT_DOMAIN), array('status' => 404));
    }
    
    $player_id = get_user_meta($user_id, PID_META_KEY, true);
    
    return array(
        'user_id' => $user_id,
        'player_id' => $player_id,
        'display_name' => $user->display_name,
    );
}

// Permission callback for viewing Player IDs
function pid_can_view_player_id($request) {
    // Allow if user is viewing their own ID or has admin capabilities
    $user_id = $request->get_param('id');
    return current_user_can(PID_EDIT_CAPABILITY) || get_current_user_id() == $user_id;
}

// Add Player ID to REST API user response
add_filter('rest_prepare_user', 'pid_add_to_rest_response', 10, 3);

function pid_add_to_rest_response($response, $user, $request) {
    // Only add if user can view
    if (current_user_can(PID_EDIT_CAPABILITY) || $user->ID === get_current_user_id()) {
        $response->data['player_id'] = get_user_meta($user->ID, PID_META_KEY, true);
    }
    
    return $response;
}