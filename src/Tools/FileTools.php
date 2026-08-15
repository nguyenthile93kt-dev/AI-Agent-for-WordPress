<?php
namespace AI_Agent\Tools;
use AI_Agent\Backup\SnapshotManager;
use AI_Agent\Security\PathValidator;
use AI_Agent\Security\SecretRedactor;
final class FileTools
{
    private $paths; private $snapshots;
    public function __construct(PathValidator $paths, SnapshotManager $snapshots) { $this->paths = $paths; $this->snapshots = $snapshots; }
    public function listDirectory(array $args): array
    {
        $path = $this->paths->validate($args['path']); if (!is_dir($path)) throw new \RuntimeException('Directory not found.');
        $entries = array(); foreach (new \DirectoryIterator($path) as $item) { if ($item->isDot() || $item->isLink()) continue; $entries[] = array('name' => $item->getFilename(), 'type' => $item->isDir() ? 'directory' : 'file', 'size' => $item->isFile() ? $item->getSize() : null); if (count($entries) >= 500) break; }
        return array('path' => $args['path'], 'entries' => $entries, 'truncated' => count($entries) >= 500);
    }
    public function readFile(array $args): array
    {
        $path = $this->paths->validate($args['path']); if (!is_file($path) || !is_readable($path)) throw new \RuntimeException('File is not readable.');
        $offset = max(0, (int) ($args['offset'] ?? 0)); $length = min(512000, max(1, (int) ($args['length'] ?? 65536)));
        $handle = fopen($path, 'rb'); fseek($handle, $offset); $content = fread($handle, $length); fclose($handle);
        return array('path' => $args['path'], 'size' => filesize($path), 'offset' => $offset, 'content' => SecretRedactor::redact((string) $content), 'truncated' => $offset + strlen((string) $content) < filesize($path));
    }
    public function searchFiles(array $args): array
    {
        $root = $this->paths->validate($args['path'] ?? 'wp-content'); if (!is_dir($root)) throw new \RuntimeException('Directory not found.');
        $query = (string) $args['query']; if ($query === '') throw new \RuntimeException('Search query is required.');
        $extension = ltrim((string) ($args['extension'] ?? ''), '.'); $results = array(); $seen = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) { if (!$file->isFile() || $file->isLink() || $file->getSize() > 512000 || ($extension && $file->getExtension() !== $extension)) continue; if (++$seen > 2000) break; $lines = @file($file->getPathname()); if (!$lines) continue; foreach ($lines as $number => $line) if (stripos($line, $query) !== false) { $results[] = array('path' => ltrim(str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path($file->getPathname())), '/'), 'line' => $number + 1, 'excerpt' => SecretRedactor::redact(trim(substr($line, 0, 300)))); if (count($results) >= 100) break 2; } }
        return array('matches' => $results, 'truncated' => count($results) >= 100 || $seen > 2000);
    }
    public function writeFile(array $args): array
    {
        $path = $this->paths->validate($args['path'], true); $content = (string) ($args['content'] ?? ''); if (strlen($content) > 2097152) throw new \RuntimeException('Write exceeds 2 MB.');
        $mode = $args['mode'] ?? 'create'; if (!in_array($mode, array('create', 'overwrite', 'append'), true)) throw new \RuntimeException('Invalid write mode.'); if ($mode === 'create' && file_exists($path)) throw new \RuntimeException('File already exists.');
        if (!empty($args['dry_run'])) return array('dry_run' => true, 'path' => $args['path'], 'bytes' => strlen($content), 'mode' => $mode);
        $snapshot = file_exists($path) ? $this->snapshots->create(array($path), 'Automatic pre-write backup') : null;
        $flags = $mode === 'append' ? FILE_APPEND | LOCK_EX : LOCK_EX; if (file_put_contents($path, $content, $flags) === false) throw new \RuntimeException('Unable to write file.');
        return array('path' => $args['path'], 'bytes' => strlen($content), 'snapshot_id' => $snapshot);
    }
    public function patchFile(array $args): array
    {
        $path = $this->paths->validate($args['path'], true); $old = file_get_contents($path); $search = (string) ($args['search'] ?? ''); if ($search === '' || substr_count($old, $search) !== 1) throw new \RuntimeException('Search text must match exactly once.');
        $new = str_replace($search, (string) ($args['replacement'] ?? ''), $old); if (!empty($args['dry_run'])) return array('dry_run' => true, 'path' => $args['path'], 'before' => $search, 'after' => $args['replacement'] ?? '');
        $snapshot = $this->snapshots->create(array($path), 'Automatic pre-patch backup'); if (file_put_contents($path, $new, LOCK_EX) === false) throw new \RuntimeException('Unable to patch file.');
        return array('path' => $args['path'], 'snapshot_id' => $snapshot, 'replacements' => 1);
    }
    public function makeDirectory(array $args): array { $path = $this->paths->validate($args['path'], true); if (!wp_mkdir_p($path)) throw new \RuntimeException('Unable to create directory.'); return array('path' => $args['path'], 'created' => true); }
}
