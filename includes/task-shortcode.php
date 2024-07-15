<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
function display_tasks_shortcode()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks';

    // Fetch tasks from the database
    $tasks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    // Start the output buffer
    ob_start();
?>

    <table class="widefat task_table fixed" cellspacing="0">
        <thead>
            <tr>
                <th class="manage-column">Title</th>
                <th class="manage-column">Description</th>
                <th class="manage-column">Assignee</th>
                <th class="manage-column">End Date</th>
                <th class="manage-column">State</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task) { ?>
                <tr>
                    <td class="column-columnname"><?php echo esc_html($task->title); ?></td>
                    <td class="column-columnname"><?php echo esc_html($task->description); ?></td>
                    <td class="column-columnname"><?php echo esc_html(get_userdata($task->user_id)->display_name); ?></td>

                    <td class="column-columnname"><?php echo $task->end_date ? date('d M Y', strtotime($task->end_date)) : ''; ?></td>
                    <td class="column-columnname">
                        <input type="checkbox" class="task-state-checkbox" data-task-id="<?php echo $task->id; ?>" <?php checked($task->state, 1); ?>>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php
    // Return the output buffer contents
    return ob_get_clean();
};
add_shortcode('display_tasks', 'display_tasks_shortcode');
