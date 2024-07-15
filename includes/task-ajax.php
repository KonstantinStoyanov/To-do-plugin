<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle AJAX request to change task state
function change_task_state()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks';

    $task_id = intval($_POST['task_id']);
    $state = intval($_POST['state']);

    $wpdb->update(
        $table_name,
        array('state' => $state),
        array('id' => $task_id),
        array('%d'),
        array('%d')
    );

    wp_send_json_success();
}
add_action('wp_ajax_change_task_state', 'change_task_state');
add_action('wp_ajax_nopriv_change_task_state', 'change_task_state');
