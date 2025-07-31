<?php
/**
 * File: includes/admin/enqueue.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: Enqueue admin assets
 */

defined('ABSPATH') || exit;

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'pid_admin_enqueue_scripts');

function pid_admin_enqueue_scripts($hook) {
    // Only load on user profile and user edit pages
    if (!in_array($hook, array('profile.php', 'user-edit.php', 'users.php'))) {
        return;
    }
    
    // Enqueue admin CSS
    wp_enqueue_style(
        'player-id-admin',
        PID_PLUGIN_URL . 'includes/assets/css/player-id-admin.css',
        array(),
        PID_PLUGIN_VERSION
    );
    
    // Only enqueue JS on profile pages where editing is possible
    if (in_array($hook, array('profile.php', 'user-edit.php')) && current_user_can(PID_EDIT_CAPABILITY)) {
        wp_enqueue_script(
            'player-id-admin',
            PID_PLUGIN_URL . 'includes/assets/js/player-id-admin.js',
            array('jquery'),
            PID_PLUGIN_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('player-id-admin', 'playerIdAdmin', array(
            'apiUrl' => rest_url(),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }
}

// Enqueue login/registration styles
add_action('login_enqueue_scripts', 'pid_login_enqueue_scripts');

function pid_login_enqueue_scripts() {
    wp_enqueue_style(
        'player-id-login',
        PID_PLUGIN_URL . 'includes/assets/css/player-id-admin.css',
        array(),
        PID_PLUGIN_VERSION
    );
}