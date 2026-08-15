<?php
namespace AI_Agent\Providers;
final class DeepSeekProvider extends OpenAIProvider
{
    public function send(array $messages, array $tools, array $options = array()): array { $formatted = array_map(static function ($tool) { return array('type' => 'function', 'function' => $tool); }, $tools); return $this->request('https://api.deepseek.com/chat/completions', array('authorization' => 'Bearer ' . $this->key), array('model' => $this->model, 'messages' => $messages, 'tools' => $formatted, 'temperature' => 0.1)); }
}
