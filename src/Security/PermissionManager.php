<?php
namespace AI_Agent\Security;

final class PermissionManager
{
    private const MAP = array('read' => 'ai_agent_read', 'content' => 'ai_agent_modify_content', 'code' => 'ai_agent_modify_files', 'database' => 'ai_agent_database', 'full' => 'ai_agent_full_access');
    public static function capabilities(): array { return array_merge(array('ai_agent_chat', 'ai_agent_manage_plugins'), array_values(self::MAP)); }
    public function allows(array $tool): bool
    {
        $settings = get_option('ai_agent_settings', array());
        if (!empty($settings['kill_switch']) && $tool['permission'] !== 'read') return false;
        return current_user_can(self::MAP[$tool['permission']] ?? 'do_not_allow');
    }
}
