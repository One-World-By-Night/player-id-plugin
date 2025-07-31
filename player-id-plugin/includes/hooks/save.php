<?php
/**
 * File: includes/hooks/save.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: Save and validation hooks for Player ID
 */

defined('ABSPATH') || exit;

// Check if user has Player ID on login
add_action('wp_login', 'pid_check_player_id_on_login', 10, 2);

function pid_check_player_id_on_login($user_login, $user) {
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    
    if (empty($player_id) && !current_user_can(PID_EDIT_CAPABILITY)) {
        // Store notice in user meta to display after redirect
        update_user_meta($user->ID, '_pid_needs_player_id', true);
    }
}

// Display notice for users without Player ID
add_action('admin_notices', 'pid_missing_player_id_notice');

function pid_missing_player_id_notice() {
    if (!is_admin()) {
        return;
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }
    
    $needs_player_id = get_user_meta($user_id, '_pid_needs_player_id', true);
    $player_id = get_user_meta($user_id, PID_META_KEY, true);
    
    if ($needs_player_id && empty($player_id)) {
        echo '<div class="notice notice-warning">';
        echo '<p><strong>' . __('Player ID Required', PID_TEXT_DOMAIN) . '</strong></p>';
        echo '<p>' . __('You need to set your Player ID. Please contact an administrator to have one assigned to you.', PID_TEXT_DOMAIN) . '</p>';
        echo '</div>';
    }
}

// Ensure Player ID is included in user data exports
add_filter('wp_privacy_personal_data_exporters', 'pid_register_exporter');

function pid_register_exporter($exporters) {
    $exporters['player-id'] = array(
        'exporter_friendly_name' => __('Player ID', PID_TEXT_DOMAIN),
        'callback' => 'pid_export_user_data',
    );
    return $exporters;
}

function pid_export_user_data($email_address) {
    $user = get_user_by('email', $email_address);
    
    if (!$user) {
        return array(
            'data' => array(),
            'done' => true,
        );
    }
    
    $player_id = get_user_meta($user->ID, PID_META_KEY, true);
    
    $export_items = array();
    
    if ($player_id) {
        $export_items[] = array(
            'group_id' => 'player-id',
            'group_label' => __('Player Information', PID_TEXT_DOMAIN),
            'item_id' => 'player-id-' . $user->ID,
            'data' => array(
                array(
                    'name' => __('Player ID', PID_TEXT_DOMAIN),
                    'value' => $player_id,
                ),
            ),
        );
    }
    
    return array(
        'data' => $export_items,
        'done' => true,
    );
}

// Prevent user deletion if they have a Player ID (optional safeguard)
add_filter('user_has_cap', 'pid_prevent_deletion_with_player_id', 10, 4);

function pid_prevent_deletion_with_player_id($allcaps, $caps, $args, $user) {
    // Only apply to delete_users capability
    if (!isset($args[0]) || $args[0] !== 'delete_users') {
        return $allcaps;
    }
    
    // Allow if user has capability to manage Player IDs
    if (isset($allcaps[PID_EDIT_CAPABILITY]) && $allcaps[PID_EDIT_CAPABILITY]) {
        return $allcaps;
    }
    
    // Check if any user being deleted has a Player ID
    if (isset($args[2])) {
        $user_id = $args[2];
        $player_id = get_user_meta($user_id, PID_META_KEY, true);
        
        if (!empty($player_id)) {
            // Remove delete capability
            $allcaps['delete_users'] = false;
        }
    }
    
    return $allcaps;
}