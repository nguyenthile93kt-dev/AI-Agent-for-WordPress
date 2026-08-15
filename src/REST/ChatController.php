<?php
namespace AI_Agent\REST;
use AI_Agent\Agent\AgentLoop;
final class ChatController
{
    private $loop; public function __construct(AgentLoop $loop) { $this->loop = $loop; }
    public function registerRoutes(): void { register_rest_route('ai-agent/v1', '/chat', array('methods' => 'POST', 'callback' => array($this, 'chat'), 'permission_callback' => static function () { return current_user_can('ai_agent_chat'); }, 'args' => array('message' => array('required' => true, 'type' => 'string'), 'mode' => array('type' => 'string', 'enum' => array('ask', 'analyze', 'edit', 'agent'), 'default' => 'analyze')))); }
    public function chat(\WP_REST_Request $request) { try { return rest_ensure_response($this->loop->run(sanitize_textarea_field($request['message']), $request['mode'])); } catch (\Throwable $error) { return new \WP_Error('ai_agent_error', $error->getMessage(), array('status' => 500)); } }
}
