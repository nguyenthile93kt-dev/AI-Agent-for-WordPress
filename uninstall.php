<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('ai_agent_settings');
foreach (array('administrator') as $roleName) { $role = get_role($roleName); if ($role) foreach (array('ai_agent_chat','ai_agent_read','ai_agent_modify_content','ai_agent_modify_files','ai_agent_database','ai_agent_manage_plugins','ai_agent_full_access') as $cap) $role->remove_cap($cap); }
