<?php
/**
 * Plugin Name: Player ID Plugin
 * Plugin URI: https://yoursite.com/player-id-plugin
 * Description: Adds a mandatory, unique Player ID field to WordPress users with wp-oauth SSO integration
 * Version: 1.0.0
 * Author: greghacke
 * Author URI: https://yoursite.com
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: player-id-plugin
 * Domain Path: /languages
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Define plugin constants
define('PID_PLUGIN_VERSION', '1.0.0');
define('PID_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PID_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PID_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load plugin includes
require_once PID_PLUGIN_PATH . 'includes/init.php';

// Activation hook
register_activation_hook(__FILE__, 'pid_plugin_activate');
function pid_plugin_activate() {
    // Add default options if needed
    update_option('pid_plugin_version', PID_PLUGIN_VERSION);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'pid_plugin_deactivate');
function pid_plugin_deactivate() {
    // Cleanup if needed
}