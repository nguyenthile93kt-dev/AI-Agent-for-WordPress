<?php
namespace AI_Agent\Tools;
use AI_Agent\Audit\TaskLogger;
use AI_Agent\Security\ApprovalManager;
use AI_Agent\Security\PermissionManager;
final class ToolExecutor
{
    private $registry; private $permissions; private $approvals; private $logger;
    public function __construct(ToolRegistry $registry, PermissionManager $permissions, ApprovalManager $approvals, TaskLogger $logger) { $this->registry = $registry; $this->permissions = $permissions; $this->approvals = $approvals; $this->logger = $logger; }
    public function execute(int $taskId, int $step, string $name, array $arguments, bool $approved = false): array
    {
        $tool = $this->registry->get($name); if (!$tool) return array('ok' => false, 'error' => 'Unknown tool.'); if (!$this->permissions->allows($tool)) return array('ok' => false, 'error' => 'Permission denied.');
        if (!$approved && $this->approvals->required($tool)) return array('ok' => false, 'approval_required' => true, 'approval_id' => $this->approvals->create($taskId, $tool, $arguments), 'tool' => $name, 'arguments' => $arguments);
        $started = microtime(true); try { $this->validate($tool['parameters'], $arguments); $data = call_user_func($tool['handler'], $arguments); $result = array('ok' => true, 'data' => $data); } catch (\Throwable $error) { $result = array('ok' => false, 'error' => $error->getMessage()); }
        $this->logger->step($taskId, $step, $name, $arguments, $result, $tool['risk'], (int) ((microtime(true) - $started) * 1000)); return $result;
    }
    private function validate(array $schema, array $arguments): void { foreach ($schema['required'] ?? array() as $key) if (!array_key_exists($key, $arguments)) throw new \InvalidArgumentException("Missing required argument: {$key}"); if (($schema['additionalProperties'] ?? true) === false) foreach ($arguments as $key => $_) if (!isset($schema['properties'][$key])) throw new \InvalidArgumentException("Unknown argument: {$key}"); }
}
