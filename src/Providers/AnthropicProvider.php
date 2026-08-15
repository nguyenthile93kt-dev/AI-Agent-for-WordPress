<?php
namespace AI_Agent\Providers;
final class AnthropicProvider extends AbstractProvider
{
    public function send(array $messages, array $tools, array $options = array()): array { $system = array_shift($messages); $formatted = array_map(static function ($tool) { return array('name' => $tool['name'], 'description' => $tool['description'], 'input_schema' => $tool['parameters']); }, $tools); return $this->request('https://api.anthropic.com/v1/messages', array('x-api-key' => $this->key, 'anthropic-version' => '2023-06-01'), array('model' => $this->model, 'system' => $system['content'] ?? '', 'messages' => $messages, 'tools' => $formatted, 'max_tokens' => 4096)); }
    public function parseToolCalls(array $response): array { $calls = array(); $text = ''; foreach ($response['content'] ?? array() as $block) { if ($block['type'] === 'text') $text .= $block['text']; elseif ($block['type'] === 'tool_use') $calls[] = array('id' => $block['id'], 'name' => $block['name'], 'arguments' => $block['input'] ?? array()); } return array('text' => $text, 'calls' => $calls, 'assistant' => array('role' => 'assistant', 'content' => $response['content'] ?? array())); }
    public function formatToolResult(string $id, array $result): array { return array('role' => 'user', 'content' => array(array('type' => 'tool_result', 'tool_use_id' => $id, 'content' => wp_json_encode($result)))); }
}
