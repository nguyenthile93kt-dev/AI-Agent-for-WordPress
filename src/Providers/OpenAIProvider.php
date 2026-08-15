<?php
namespace AI_Agent\Providers;
class OpenAIProvider extends AbstractProvider
{
    public function send(array $messages, array $tools, array $options = array()): array { $formatted = array_map(static function ($tool) { return array('type' => 'function', 'function' => $tool); }, $tools); return $this->request('https://api.openai.com/v1/chat/completions', array('authorization' => 'Bearer ' . $this->key), array('model' => $this->model, 'messages' => $messages, 'tools' => $formatted, 'temperature' => 0.1)); }
    public function parseToolCalls(array $response): array { $message = $response['choices'][0]['message'] ?? array(); $calls = array_map(static function ($call) { return array('id' => $call['id'], 'name' => $call['function']['name'], 'arguments' => json_decode($call['function']['arguments'], true) ?: array()); }, $message['tool_calls'] ?? array()); return array('text' => (string) ($message['content'] ?? ''), 'calls' => $calls, 'assistant' => $message); }
    public function formatToolResult(string $id, array $result): array { return array('role' => 'tool', 'tool_call_id' => $id, 'content' => wp_json_encode($result)); }
}
