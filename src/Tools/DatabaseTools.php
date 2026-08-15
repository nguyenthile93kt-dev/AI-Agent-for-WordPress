<?php
namespace AI_Agent\Tools;
use AI_Agent\Security\SqlValidator;
final class DatabaseTools
{
    private $validator; public function __construct(SqlValidator $validator) { $this->validator = $validator; }
    public function tables(): array { global $wpdb; return array('tables' => $wpdb->get_col('SHOW TABLES LIKE ' . $wpdb->prepare('%s', $wpdb->esc_like($wpdb->prefix) . '%'))); }
    public function schema(array $args): array { global $wpdb; $table = (string) ($args['table'] ?? ''); if (!preg_match('/^' . preg_quote($wpdb->prefix, '/') . '[A-Za-z0-9_]+$/', $table)) throw new \RuntimeException('Table is outside the WordPress prefix.'); return array('table' => $table, 'columns' => $wpdb->get_results("DESCRIBE `{$table}`", ARRAY_A), 'indexes' => $wpdb->get_results("SHOW INDEX FROM `{$table}`", ARRAY_A)); }
    public function select(array $args): array
    {
        global $wpdb; $query = (string) $args['query']; $this->validator->read($query); $query = $this->prepare($query, $args['params'] ?? array()); $limit = min(1000, max(1, (int) ($args['limit'] ?? 1000))); $rows = $wpdb->get_results($query, ARRAY_A); if ($wpdb->last_error) throw new \RuntimeException($wpdb->last_error); return array('rows' => array_slice($rows, 0, $limit), 'count' => min(count($rows), $limit), 'truncated' => count($rows) > $limit);
    }
    public function execute(array $args): array { global $wpdb; $query = (string) $args['query']; $this->validator->mutation($query); $result = $wpdb->query($this->prepare($query, $args['params'] ?? array())); if ($result === false) throw new \RuntimeException($wpdb->last_error); return array('affected_rows' => (int) $result, 'insert_id' => (int) $wpdb->insert_id); }
    private function prepare(string $query, array $params): string { global $wpdb; if (!$params) return $query; return $wpdb->prepare($query, $params); }
}
