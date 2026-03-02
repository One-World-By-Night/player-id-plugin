<?php

defined('ABSPATH') || exit;

define('PID_TEXT_DOMAIN', 'player-id-plugin');

define('PID_META_KEY', 'player_id');

define('PID_EDIT_CAPABILITY', 'manage_options');

add_action('init', 'pid_plugin_init');
function pid_plugin_init() {
    load_plugin_textdomain(PID_TEXT_DOMAIN, false, dirname(plugin_basename(PID_PLUGIN_BASENAME)) . '/languages');
}