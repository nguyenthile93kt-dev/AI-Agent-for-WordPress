<?php
namespace AI_Agent\Audit;
final class TaskLogger
{
    public static function install(): void
    {
        global $wpdb; require_once ABSPATH . 'wp-admin/includes/upgrade.php'; $charset = $wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$wpdb->prefix}ai_agent_tasks (id bigint unsigned NOT NULL AUTO_INCREMENT,user_id bigint unsigned NOT NULL,provider varchar(30) NOT NULL,model varchar(100) NOT NULL,prompt longtext NOT NULL,status varchar(30) NOT NULL,mode varchar(20) NOT NULL,started_at datetime NOT NULL,finished_at datetime NULL,token_usage bigint unsigned NOT NULL DEFAULT 0,estimated_cost decimal(12,6) NOT NULL DEFAULT 0,PRIMARY KEY (id),KEY status (status)) {$charset};");
        dbDelta("CREATE TABLE {$wpdb->prefix}ai_agent_steps (id bigint unsigned NOT NULL AUTO_INCREMENT,task_id bigint unsigned NOT NULL,step int unsigned NOT NULL,tool varchar(100) NOT NULL,arguments longtext NOT NULL,result longtext NOT NULL,risk varchar(20) NOT NULL,duration int unsigned NOT NULL DEFAULT 0,created_at datetime NOT NULL,PRIMARY KEY (id),KEY task_id (task_id)) {$charset};");
        dbDelta("CREATE TABLE {$wpdb->prefix}ai_agent_approvals (id bigint unsigned NOT NULL AUTO_INCREMENT,task_id bigint unsigned NOT NULL,tool varchar(100) NOT NULL,arguments longtext NOT NULL,status varchar(20) NOT NULL,approved_by bigint unsigned NULL,created_at datetime NOT NULL,PRIMARY KEY (id),KEY task_id (task_id)) {$charset};");
    }
    public function start(string $provider, string $model, string $prompt, string $mode): int { global $wpdb; $wpdb->insert($wpdb->prefix . 'ai_agent_tasks', array('user_id' => get_current_user_id(), 'provider' => $provider, 'model' => $model, 'prompt' => $prompt, 'status' => 'running', 'mode' => $mode, 'started_at' => current_time('mysql', true))); return (int) $wpdb->insert_id; }
    public function step(int $taskId, int $step, string $tool, array $arguments, array $result, string $risk, int $duration): void { global $wpdb; $wpdb->insert($wpdb->prefix . 'ai_agent_steps', array('task_id' => $taskId, 'step' => $step, 'tool' => $tool, 'arguments' => wp_json_encode($arguments), 'result' => wp_json_encode($result), 'risk' => $risk, 'duration' => $duration, 'created_at' => current_time('mysql', true))); }
    public function finish(int $id, string $status): void { global $wpdb; $wpdb->update($wpdb->prefix . 'ai_agent_tasks', array('status' => $status, 'finished_at' => current_time('mysql', true)), array('id' => $id)); }
}
