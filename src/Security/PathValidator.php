<?php
namespace AI_Agent\Security;
final class PathValidator
{
    public function validate(string $path, bool $write = false): string
    {
        if ($path === '' || strpos(str_replace('\\', '/', $path), '../') !== false || strpos($path, "\0") !== false) throw new \RuntimeException('Unsafe path.');
        $root = rtrim(wp_normalize_path(ABSPATH), '/');
        $candidate = wp_normalize_path($path[0] === '/' ? $path : $root . '/' . ltrim($path, '/'));
        $parent = is_dir($candidate) ? realpath($candidate) : realpath(dirname($candidate));
        if ($parent === false || strpos(wp_normalize_path($parent), $root) !== 0) throw new \RuntimeException('Path is outside WordPress.');
        $relative = ltrim(substr($candidate, strlen($root)), '/');
        if (preg_match('~(^|/)(wp-config\.php|\.env(?:\..*)?|\.htaccess|id_rsa|\.ssh)(/|$)~i', $relative)) throw new \RuntimeException('Protected path.');
        if ($write && (strpos($relative, 'wp-content/') !== 0 || preg_match('~^(wp-admin|wp-includes)/~', $relative))) throw new \RuntimeException('Writes are limited to wp-content.');
        return $candidate;
    }
}
