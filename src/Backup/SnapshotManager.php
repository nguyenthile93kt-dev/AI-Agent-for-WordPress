<?php
namespace AI_Agent\Backup;
use AI_Agent\Security\PathValidator;
final class SnapshotManager
{
    public function create(array $files, string $description = ''): string
    {
        $id = 'agt_' . gmdate('Ymd_His') . '_' . wp_generate_password(6, false, false); $root = WP_CONTENT_DIR . '/ai-agent-snapshots/' . $id; wp_mkdir_p($root); $manifest = array('id' => $id, 'description' => $description, 'created_at' => gmdate('c'), 'files' => array());
        $validator = new PathValidator(); foreach ($files as $file) { $absolute = $validator->validate($file); if (!is_file($absolute)) continue; $relative = ltrim(str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path($absolute)), '/'); $target = $root . '/files/' . $relative; wp_mkdir_p(dirname($target)); if (!copy($absolute, $target)) throw new \RuntimeException('Snapshot copy failed.'); $manifest['files'][] = array('path' => $relative, 'sha256' => hash_file('sha256', $absolute)); }
        file_put_contents($root . '/manifest.json', wp_json_encode($manifest, JSON_PRETTY_PRINT), LOCK_EX); return $id;
    }
    public function createFromTool(array $args): array { return array('snapshot_id' => $this->create($args['files'] ?? array(), (string) ($args['description'] ?? ''))); }
    public function rollbackFromTool(array $args): array
    {
        $id = (string) $args['snapshot_id']; if (!preg_match('/^agt_[A-Za-z0-9_]+$/', $id)) throw new \RuntimeException('Invalid snapshot id.'); $root = WP_CONTENT_DIR . '/ai-agent-snapshots/' . $id; $manifest = json_decode((string) @file_get_contents($root . '/manifest.json'), true); if (!$manifest) throw new \RuntimeException('Snapshot not found.'); $validator = new PathValidator(); $restored = array(); foreach ($manifest['files'] as $file) { $target = $validator->validate($file['path'], true); $source = $root . '/files/' . $file['path']; if (!is_readable($source) || !copy($source, $target)) throw new \RuntimeException('Snapshot restore failed.'); $restored[] = $file['path']; } return array('snapshot_id' => $id, 'restored' => $restored);
    }
}
