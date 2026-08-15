<?php
namespace AI_Agent\REST;
use AI_Agent\Tools\ToolExecutor;
final class ApprovalController
{
    private $executor; public function __construct(ToolExecutor $executor) { $this->executor = $executor; }
    public function registerRoutes(): void { register_rest_route('ai-agent/v1', '/approvals/(?P<id>\d+)/approve', array('methods' => 'POST', 'callback' => array($this, 'approve'), 'permission_callback' => static function () { return current_user_can('ai_agent_full_access'); })); }
    public function approve(\WP_REST_Request $request)
    {
        global $wpdb; $table = $wpdb->prefix . 'ai_agent_approvals'; $approval = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d AND status = 'pending'", (int) $request['id']), ARRAY_A); if (!$approval) return new \WP_Error('not_found', 'Pending approval not found.', array('status' => 404));
        $arguments = json_decode($approval['arguments'], true); if (!is_array($arguments)) return new \WP_Error('invalid_approval', 'Stored arguments are invalid.', array('status' => 500));
        $result = $this->executor->execute((int) $approval['task_id'], 0, $approval['tool'], $arguments, true); $wpdb->update($table, array('status' => !empty($result['ok']) ? 'approved' : 'failed', 'approved_by' => get_current_user_id()), array('id' => (int) $approval['id'])); return rest_ensure_response($result);
    }
}
