<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
function task_manager_menu()
{
    add_menu_page('Task Manager', 'Task Manager', 'manage_options', 'task-manager', 'task_manager_page', 'dashicons-list-view', 6);
}
add_action('admin_menu', 'task_manager_menu');

// Display the admin page
function task_manager_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks';

    // Handle form submissions (edit, delete, add)
    if (isset($_POST['submit'])) {
        if (isset($_POST['edit_task']) && $_POST['edit_task'] == 1) {
            $id = $_POST['id'];
            $title = sanitize_text_field($_POST['title']);
            $description = sanitize_textarea_field($_POST['description']);
            $user_id = absint($_POST['user_id']);
            $state = isset($_POST['state']) ? 1 : 0;
            $end_date = sanitize_text_field($_POST['end_date']);

            // Validate and format end date as d m Y
            if (!empty($end_date)) {
                $end_date = DateTime::createFromFormat('d m Y', $end_date);
                if ($end_date) {
                    $end_date = $end_date->format('Y-m-d');
                } else {
                    $end_date = null;
                }
            } else {
                $end_date = null;
            }

            $wpdb->update(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'title' => $title,
                    'description' => $description,
                    'updated_at' => current_time('mysql'),
                    'state' => $state,
                    'end_date' => $end_date,
                ),
                array('id' => $id),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'
                ),
                array('%d')
            );
            echo '<div class="updated"><p>Task updated successfully!</p></div>';
        }

        // Delete task
        if (isset($_POST['delete_task']) && $_POST['delete_task'] == 1) {
            $id = $_POST['id'];
            $result = $wpdb->delete(
                $table_name,
                array('id' => $id),
                array('%d')
            );

            if ($result === false) {
                echo '<div class="error"><p>Error deleting task: ' . $wpdb->last_error . '</p></div>';
            } else {
                echo '<div class="updated"><p>Task deleted successfully!</p></div>';
            }
        }

        // Add new task
        if (isset($_POST['new_task']) && $_POST['new_task'] == 1) {
            $title = sanitize_text_field($_POST['title']);
            $description = sanitize_textarea_field($_POST['description']);
            $user_id = absint($_POST['user_id']);
            $state = isset($_POST['state']) ? 1 : 0;
            $end_date = sanitize_text_field($_POST['end_date']);

            // Validate and format end date as d m Y
            if (!empty($end_date)) {
                $end_date = DateTime::createFromFormat('d m Y', $end_date);
                if ($end_date) {
                    $end_date = $end_date->format('Y-m-d');
                } else {
                    $end_date = null;
                }
            } else {
                $end_date = null;
            }

            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'title' => $title,
                    'description' => $description,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'state' => $state,
                    'end_date' => $end_date,
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'
                )
            );
            echo '<div class="updated"><p>New task added successfully!</p></div>';
        }
    }

    // Fetch tasks
    $tasks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    // Include the admin page HTML
    include TODO_PLUGIN_DIR . 'includes/admin-page.php';
}
