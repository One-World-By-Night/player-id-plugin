<?php
/**
 * File: includes/init.php
 * Text Domain: player-id-plugin
 * @version 1.0.0
 * @author Your Name
 * Function: Load all plugin components
 */

defined('ABSPATH') || exit;

// Load core files first
require_once __DIR__ . '/core/bootstrap.php';

// Load field management
require_once __DIR__ . '/fields.php';

// Load admin functionality
require_once __DIR__ . '/admin/settings.php';
require_once __DIR__ . '/admin/enqueue.php';

// Load hooks
require_once __DIR__ . '/hooks/save.php';
require_once __DIR__ . '/hooks/rest-api.php';

// Fire loaded action
do_action('pid_plugin_loaded');