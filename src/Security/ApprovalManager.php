<?php
namespace AI_Agent\Security;
final class ApprovalManager
{
    public function required(array $tool): bool { return in_array($tool['risk'], array('high', 'critical'), true); }
    public function create(int $taskId, array $tool, array $arguments): int
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'ai_agent_approvals', array('task_id' => $taskId, 'tool' => $tool['name'], 'arguments' => wp_json_encode($arguments), 'status' => 'pending', 'created_at' => current_time('mysql', true)));
        return (int) $wpdb->insert_id;
    }
}
