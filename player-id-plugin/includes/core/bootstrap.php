<?php
/**
 * File: includes/core/bootstrap.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author greghacke
 * Function: Initialize plugin constants and core functionality
 */

defined('ABSPATH') || exit;

// Plugin text domain for translations
define('PID_TEXT_DOMAIN', 'player-id-plugin');

// Meta key for storing player ID
define('PID_META_KEY', 'player_id');

// Capability required to edit player IDs
define('PID_EDIT_CAPABILITY', 'manage_options');

// Initialize plugin
add_action('init', 'pid_plugin_init');
function pid_plugin_init() {
    load_plugin_textdomain(PID_TEXT_DOMAIN, false, dirname(plugin_basename(PID_PLUGIN_BASENAME)) . '/languages');
}