<?php
namespace AI_Agent\Tools;

final class ToolRegistry
{
    private $tools = array();

    public static function fromCompact(array $item): array
    {
        list($name, $description, $permission, $risk, $category, $handler, $properties) = $item;
        $required = array();
        $schema = array();
        foreach ($properties as $key => $type) {
            $schema[$key] = array('type' => $type);
            if (in_array($key, array('path', 'query', 'resource', 'action', 'key', 'plugin', 'snapshot_id'), true)) $required[] = $key;
        }
        return compact('name', 'description', 'permission', 'risk', 'category', 'handler') + array(
            'tags' => array($category, $permission, $risk),
            'parameters' => array('type' => 'object', 'properties' => $schema, 'required' => array_values(array_unique($required)), 'additionalProperties' => false),
            'timeout' => 30,
        );
    }

    public function register(array $tool): void
    {
        foreach (array('name', 'description', 'parameters', 'permission', 'risk', 'handler') as $key) {
            if (!isset($tool[$key])) throw new \InvalidArgumentException("Tool is missing {$key}");
        }
        if (!preg_match('/^[a-z][a-z0-9_]{1,63}$/', $tool['name']) || !is_callable($tool['handler'])) {
            throw new \InvalidArgumentException('Invalid tool definition');
        }
        $this->tools[$tool['name']] = $tool;
    }

    public function applyExtensions(): void
    {
        if (!function_exists('apply_filters')) return;
        foreach ((array) apply_filters('ai_agent_tools', array()) as $tool) $this->register($tool);
    }

    public function get(string $name): ?array { return $this->tools[$name] ?? null; }

    public function discover(string $mode, string $message = ''): array
    {
        $levels = array('ask' => array(), 'analyze' => array('read'), 'edit' => array('read', 'content', 'code'), 'agent' => array('read', 'content', 'code', 'database', 'full'));
        $allowed = $levels[$mode] ?? array();
        $keywords = strtolower($message);
        return array_values(array_filter($this->tools, static function ($tool) use ($allowed, $keywords) {
            if (!in_array($tool['permission'], $allowed, true)) return false;
            if ($keywords === '') return true;
            $categoryHints = array('database' => 'database sql query table slow option transient', 'file' => 'file code plugin theme error php fix create', 'plugin' => 'plugin extension', 'wordpress' => 'post page user comment option content', 'diagnostic' => 'error slow health system php', 'backup' => 'snapshot backup rollback restore fix edit');
            $hints = $categoryHints[$tool['category']] ?? '';
            foreach (preg_split('/\s+/', $hints) as $hint) if ($hint && strpos($keywords, $hint) !== false) return true;
            return $tool['permission'] === 'read';
        }));
    }

    public static function providerSchemas(array $tools): array
    {
        return array_map(static function ($tool) {
            return array('name' => $tool['name'], 'description' => $tool['description'], 'parameters' => $tool['parameters']);
        }, $tools);
    }
}
