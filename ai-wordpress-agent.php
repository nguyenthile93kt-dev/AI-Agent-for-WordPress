<?php
/**
 * Plugin Name: AI Agent for WordPress
 * Description: Provider-independent, permission-aware AI tooling for WordPress administrators.
 * Version: 0.1.0
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('AI_AGENT_VERSION', '0.1.0');
define('AI_AGENT_FILE', __FILE__);
define('AI_AGENT_DIR', plugin_dir_path(__FILE__));

spl_autoload_register(static function ($class) {
    $prefix = 'AI_Agent\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $path = AI_AGENT_DIR . 'src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
});

register_activation_hook(__FILE__, array('AI_Agent\\Plugin', 'activate'));
add_action('plugins_loaded', static function () {
    AI_Agent\Plugin::instance()->boot();
});
