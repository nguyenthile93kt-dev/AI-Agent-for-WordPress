<?php
namespace AI_Agent\Tools;
use AI_Agent\Security\SecretRedactor;
final class DiagnosticTools
{
    public function systemInfo(): array { global $wpdb; $theme = wp_get_theme(); return array('wordpress' => get_bloginfo('version'), 'php' => PHP_VERSION, 'database' => $wpdb->db_version(), 'memory_limit' => ini_get('memory_limit'), 'active_theme' => $theme->get('Name') . ' ' . $theme->get('Version'), 'debug' => defined('WP_DEBUG') && WP_DEBUG, 'multisite' => is_multisite()); }
    public function phpErrors(array $args): array { $path = WP_CONTENT_DIR . '/debug.log'; if (!is_readable($path)) return array('entries' => array(), 'message' => 'debug.log is unavailable.'); $limit = min(200, max(1, (int) ($args['limit'] ?? 50))); $lines = file($path, FILE_IGNORE_NEW_LINES); $filter = (string) ($args['filter'] ?? ''); if ($filter !== '') $lines = array_values(array_filter($lines, static function ($line) use ($filter) { return stripos($line, $filter) !== false; })); return array('entries' => array_map(array(SecretRedactor::class, 'redact'), array_slice($lines, -$limit))); }
}
