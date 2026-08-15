<?php
namespace AI_Agent\Security;
final class SqlValidator
{
    public function read(string $query): void
    {
        $normalized = $this->normalize($query);
        if (!preg_match('/^(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i', $normalized) || preg_match('/\b(INTO\s+(OUT|DUMP)FILE|LOAD_FILE|SLEEP|BENCHMARK)\b/i', $normalized)) throw new \RuntimeException('Only safe read queries are allowed.');
    }
    public function mutation(string $query): void
    {
        $normalized = $this->normalize($query);
        if (!preg_match('/^(INSERT|UPDATE|DELETE|CREATE\s+TABLE|ALTER\s+TABLE)\b/i', $normalized) || preg_match('/\b(DROP\s+(DATABASE|TABLE)|TRUNCATE|GRANT|REVOKE|LOAD\s+DATA|INTO\s+OUTFILE)\b/i', $normalized)) throw new \RuntimeException('SQL operation is not allowed.');
    }
    private function normalize(string $query): string
    {
        $trimmed = trim($query); if (strpos(rtrim($trimmed, ';'), ';') !== false) throw new \RuntimeException('Multiple SQL statements are not allowed.');
        return trim(preg_replace('~/\*.*?\*/|--[^\r\n]*|#[^\r\n]*~s', '', $query));
    }
}
