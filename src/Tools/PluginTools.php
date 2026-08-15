<?php
namespace AI_Agent\Tools;
final class PluginTools
{
    private function load(): void { if (!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
    public function listPlugins(): array { $this->load(); $active = get_option('active_plugins', array()); $items = array(); foreach (get_plugins() as $path => $data) $items[] = array('path' => $path, 'name' => $data['Name'], 'version' => $data['Version'], 'active' => in_array($path, $active, true) || is_plugin_active_for_network($path)); return array('plugins' => $items); }
    public function activate(array $args): array { $this->load(); $error = activate_plugin(plugin_basename($args['plugin'])); if (is_wp_error($error)) throw new \RuntimeException($error->get_error_message()); return array('plugin' => plugin_basename($args['plugin']), 'active' => true); }
    public function deactivate(array $args): array { $this->load(); deactivate_plugins(plugin_basename($args['plugin'])); return array('plugin' => plugin_basename($args['plugin']), 'active' => false); }
}
