<?php
namespace AI_Agent\Providers;
interface ProviderInterface
{
    public function send(array $messages, array $tools, array $options = array()): array;
    public function parseToolCalls(array $response): array;
    public function formatToolResult(string $toolCallId, array $result): array;
}
