<?php
namespace AI_Agent\Providers;
abstract class AbstractProvider implements ProviderInterface
{
    protected $key; protected $model;
    public function __construct(string $key, string $model) { $this->key = $key; $this->model = $model; }
    protected function request(string $url, array $headers, array $body): array { $response = wp_remote_post($url, array('timeout' => 60, 'headers' => $headers + array('content-type' => 'application/json'), 'body' => wp_json_encode($body))); if (is_wp_error($response)) throw new \RuntimeException($response->get_error_message()); $status = wp_remote_retrieve_response_code($response); $decoded = json_decode(wp_remote_retrieve_body($response), true); if ($status < 200 || $status >= 300) throw new \RuntimeException('Provider error (' . $status . '): ' . sanitize_text_field($decoded['error']['message'] ?? 'Unknown error')); return $decoded; }
}
