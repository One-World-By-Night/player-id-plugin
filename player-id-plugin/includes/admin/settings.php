<?php
/**
 * File: includes/admin/settings.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author greghacke
 * Function: Admin interface for Player ID management
 */

defined('ABSPATH') || exit;

// Add Player ID column to users list
add_filter('manage_users_columns', 'pid_add_user_column');

function pid_add_user_column($columns) {
    $columns['player_id'] = __('Player ID', PID_TEXT_DOMAIN);
    return $columns;
}

// Display Player ID in users list
add_filter('manage_users_custom_column', 'pid_show_user_column_content', 10, 3);

function pid_show_user_column_content($value, $column_name, $user_id) {
    if ('player_id' === $column_name) {
        $player_id = get_user_meta($user_id, PID_META_KEY, true);
        return $player_id ? esc_html($player_id) : '<em>' . __('Not set', PID_TEXT_DOMAIN) . '</em>';
    }
    return $value;
}

// Make Player ID column sortable
add_filter('manage_users_sortable_columns', 'pid_sortable_columns');

function pid_sortable_columns($columns) {
    $columns['player_id'] = 'player_id';
    return $columns;
}

// Handle Player ID sorting
add_action('pre_get_users', 'pid_sort_by_player_id');

function pid_sort_by_player_id($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('player_id' === $orderby) {
        $query->set('meta_key', PID_META_KEY);
        $query->set('orderby', 'meta_value');
    }
}

// Add bulk action to check for missing Player IDs
add_filter('bulk_actions-users', 'pid_bulk_actions');

function pid_bulk_actions($bulk_actions) {
    $bulk_actions['check_player_ids'] = __('Check Player IDs', PID_TEXT_DOMAIN);
    return $bulk_actions;
}

// Handle bulk action
add_filter('handle_bulk_actions-users', 'pid_handle_bulk_action', 10, 3);

function pid_handle_bulk_action($redirect_to, $action, $user_ids) {
    if ($action !== 'check_player_ids') {
        return $redirect_to;
    }
    
    $missing = 0;
    foreach ($user_ids as $user_id) {
        $player_id = get_user_meta($user_id, PID_META_KEY, true);
        if (empty($player_id)) {
            $missing++;
        }
    }
    
    $redirect_to = add_query_arg('player_ids_missing', $missing, $redirect_to);
    return $redirect_to;
}

// Display admin notices
add_action('admin_notices', 'pid_admin_notices');

function pid_admin_notices() {
    if (!empty($_GET['player_ids_missing'])) {
        $missing = intval($_GET['player_ids_missing']);
        if ($missing > 0) {
            echo '<div class="notice notice-warning is-dismissible"><p>';
            printf(
                _n('%d user is missing a Player ID.', '%d users are missing Player IDs.', $missing, PID_TEXT_DOMAIN),
                $missing
            );
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>';
            _e('All selected users have Player IDs.', PID_TEXT_DOMAIN);
            echo '</p></div>';
        }
    }
}