<?php
namespace AI_Agent\Tools;
final class WordPressTools
{
    public function get(array $args): array
    {
        $resource = $args['resource']; $query = $args['query'] ?? array();
        if (in_array($resource, array('post', 'page'), true)) { $query['post_type'] = $resource === 'page' ? 'page' : ($query['post_type'] ?? 'post'); $query['posts_per_page'] = min(100, (int) ($query['posts_per_page'] ?? 20)); return array('items' => get_posts($query)); }
        if ($resource === 'user') return array('items' => get_users(array_merge($query, array('number' => min(100, (int) ($query['number'] ?? 20))))));
        if ($resource === 'comment') return array('items' => get_comments(array_merge($query, array('number' => min(100, (int) ($query['number'] ?? 20))))));
        if ($resource === 'option') return array('value' => $this->option(array('action' => 'get', 'key' => $query['key'] ?? ''))['value']);
        throw new \RuntimeException('Unsupported resource.');
    }
    public function create(array $args): array { $resource = $args['resource']; if (in_array($resource, array('post', 'page'), true)) { $data = $args['data'] ?? array(); if ($resource === 'page') $data['post_type'] = 'page'; $id = wp_insert_post(wp_slash($data), true); } elseif ($resource === 'comment') $id = wp_insert_comment(wp_slash($args['data'] ?? array())); else throw new \RuntimeException('Unsupported resource.'); if (is_wp_error($id)) throw new \RuntimeException($id->get_error_message()); return array('id' => (int) $id); }
    public function update(array $args): array { $data = $args['data'] ?? array(); $data['ID'] = (int) $args['id']; if (in_array($args['resource'], array('post', 'page'), true)) $result = wp_update_post(wp_slash($data), true); elseif ($args['resource'] === 'user') $result = wp_update_user(wp_slash($data)); elseif ($args['resource'] === 'comment') $result = wp_update_comment(wp_slash($data), true); else throw new \RuntimeException('Unsupported resource.'); if (is_wp_error($result)) throw new \RuntimeException($result->get_error_message()); return array('id' => (int) $args['id'], 'updated' => (bool) $result); }
    public function delete(array $args): array { if (in_array($args['resource'], array('post', 'page'), true)) $result = wp_delete_post((int) $args['id'], !empty($args['force'])); elseif ($args['resource'] === 'comment') $result = wp_delete_comment((int) $args['id'], !empty($args['force'])); else throw new \RuntimeException('Unsupported resource.'); return array('deleted' => (bool) $result); }
    public function option(array $args): array { $key = (string) ($args['key'] ?? ''); if ($key === '' || preg_match('/(password|secret|token|api.?key|auth.?key|_key$)/i', $key) || strpos($key, 'ai_agent') === 0) throw new \RuntimeException('Protected option.'); if ($args['action'] === 'get') return array('value' => get_option($key)); if ($args['action'] === 'set') return array('updated' => update_option($key, $args['value'] ?? null)); if ($args['action'] === 'delete') return array('deleted' => delete_option($key)); throw new \RuntimeException('Unsupported option action.'); }
}
