<?
/*
* Plugin Name: TO-DO list plugin for Blue Window Ltd
* Description: WordPress PHP Challenge: To-Do List Plugin
* Author: Konstantin Stoyanov
*/



// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue styles and task manager scripts for both admin and front-end
function task_manager_scripts()
{
    // Common styles
    wp_enqueue_style('task-manager', plugins_url('/assets/css/todo-plugin.css', __FILE__));

    // Common scripts
    wp_enqueue_script('task-manager', plugins_url('/assets/js/todo-plugin.js', __FILE__), array('jquery'), null, true);

    // Localize script for AJAX
    wp_localize_script('task-manager', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'task_manager_scripts');
add_action('admin_enqueue_scripts', 'task_manager_scripts');

function enqueue_datepicker()
{
    // Enqueue jQuery UI Datepicker
    wp_enqueue_script('jquery-ui-datepicker');

    // Enqueue jQuery UI theme
    wp_enqueue_style('jquery-ui-theme', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}

add_action('wp_enqueue_scripts', 'enqueue_datepicker');
add_action('admin_enqueue_scripts', 'enqueue_datepicker');


global $task_manager_db_version;
$task_manager_db_version = '1.0';

function task_manager_install()
{
    global $wpdb;
    global $task_manager_db_version;

    $table_name = $wpdb->prefix . 'tasks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        state boolean NOT NULL,
        end_date datetime DEFAULT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";



    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('task_manager_db_version', $task_manager_db_version);
}

register_activation_hook(__FILE__, 'task_manager_install');

function task_manager_update_db_check()
{
    global $task_manager_db_version;
    if (get_site_option('task_manager_db_version') != $task_manager_db_version) {
        task_manager_install();
    }
}
add_action('plugins_loaded', 'task_manager_update_db_check');

function task_manager_deactivate()
{
    // Deactivation logic here (if needed)
}
register_deactivation_hook(__FILE__, 'task_manager_deactivate');

function task_manager_uninstall()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    delete_option('task_manager_db_version');
}
register_uninstall_hook(__FILE__, 'task_manager_uninstall');


function task_manager_register_post_type()
{
    register_post_type('task', [
        'labels' => [
            'name' => 'Tasks',
            'singular_name' => 'Task'
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true, // Important for Gutenberg
        'supports' => ['title', 'editor']
    ]);
}
add_action('init', 'task_manager_register_post_type');

function task_manager_menu()
{
    add_menu_page(
        'Task Manager', // Page title
        'Task Manager', // Menu title
        'manage_options', // Capability
        'task-manager', // Menu slug
        'task_manager_page', // Function to display the page
        'dashicons-list-view', // Icon URL
        20 // Position
    );
}


add_action('admin_menu', 'task_manager_menu');


// Function to render the admin page for task management.
function task_manager_page()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'tasks';

    // Handle form submissions for adding, editing, and deleting tasks.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Add new task.
        if (isset($_POST['new_task']) && $_POST['new_task'] == 1) {
            $title = sanitize_text_field($_POST['title']);
            $description = sanitize_textarea_field($_POST['description']);
            $user_id = absint($_POST['user_id']);
            $end_date = sanitize_text_field($_POST['end_date']);

            // Validate and format end date as d m Y.
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
                    'state' => 0,
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



        // Edit task.
        if (isset($_POST['edit_task']) && $_POST['edit_task'] == '1') {

            $id = $_POST['id'];
            $title = sanitize_text_field($_POST['title']);
            $description = sanitize_textarea_field($_POST['description']);
            $user_id = absint($_POST['user_id']);
            $state = isset($_POST['state']) ? 1 : 0;
            $end_date = sanitize_text_field($_POST['end_date']);

            // Validate and format end date as d m Y.
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
        // Delete task.
        if (isset($_POST['delete_task']) && $_POST['delete_task'] == '1') {

            $id = absint($_POST['id']);

            $result = $wpdb->delete(
                $table_name,
                array('id' => $id)
            );

            if ($result === false) {
                echo '<div class="error"><p>Error deleting task: ' . $wpdb->last_error . '</p></div>';
            } else {
                echo '<div class="updated"><p>Task deleted successfully!</p></div>';
            }
        }
    }

    // Fetch tasks from the database.
    $tasks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
?>

    <div class="wrap">
        <h1>Task Manager</h1>

        <h2>Add New Task</h2>
        <form method="POST">
            <input type="hidden" name="new_task" value="1">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="title">Title</label></th>
                    <td><input name="title" type="text" id="title" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="description">Description</label></th>
                    <td><textarea name="description" id="description" rows="5" class="large-text" required></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="user_id">Assign To</label></th>
                    <td>
                        <select name="user_id" id="user_id" required>
                            <?php
                            $users = get_users();
                            foreach ($users as $user) {
                                echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="end_date">End Date (d m Y)</label></th>
                    <td><input name="end_date" type="text" id="end_date" class="regular-text datepicker" placeholder="dd mm yyyy" required></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add Task"></p>
        </form>

        <h2>Existing Tasks</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Title</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Description</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Assignee</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">State</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">End Date</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($tasks as $task) { ?>
                    <tr>
                        <td class="column-columnname"><?php echo esc_html($task->title); ?></td>
                        <td class="column-columnname"><?php echo esc_html($task->description); ?></td>
                        <td class="column-columnname"><?php echo get_userdata($task->user_id)->display_name; ?></td>
                        <td class="column-columnname"><?php echo $task->state ? 'Completed' : 'Pending'; ?></td>
                        <td class="column-columnname"><?php echo $task->end_date ? date('d M Y', strtotime($task->end_date)) : ''; ?></td>
                        <td class="column-columnname">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_task" value="1">
                                <input type="hidden" name="id" value="<?php echo $task->id; ?>">
                                <button type="submit" class="button button-link-delete" onclick="return confirm('Are you sure you want to delete this task?');">Delete</button>
                            </form>
                            <button class="button button-link-edit" onclick="openEditForm(<?php echo $task->id; ?>, '<?php echo esc_js($task->title); ?>', '<?php echo esc_js($task->description); ?>', '<?php echo esc_js($task->user_id); ?>', '<?php echo esc_js($task->state); ?>', '<?php echo esc_js($task->end_date); ?>')">Edit</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div id="editTaskModal" style="display:none;">
            <h2>Edit Task</h2>
            <form method="POST">
                <input type="hidden" name="edit_task" value="1">
                <input type="hidden" name="id" id="edit_task_id" value="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit_title">Title</label></th>
                        <td><input name="title" type="text" id="edit_title" value="" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_description">Description</label></th>
                        <td><textarea name="description" id="edit_description" rows="5" class="large-text" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_user_id">Assign To</label></th>
                        <td>
                            <select name="user_id" id="edit_user_id" required>
                                <?php
                                $users = get_users();
                                foreach ($users as $user) {
                                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_state">State</label></th>
                        <td><input type="checkbox" name="state" id="edit_state" value="1"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit_end_date">End Date (d m Y)</label></th>
                        <td><input name="end_date" type="text" id="edit_end_date" class="regular-text datepicker" placeholder="dd mm yyyy"></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Task"></p>

            </form>
            <button type="button" class="button close">&times;</button>

        </div>
    </div>

    <script>
        function openEditForm(id, title, description, user_id, state, end_date) {
            document.getElementById('edit_task_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_user_id').value = user_id;
            document.getElementById('edit_state').checked = state == 1;
            document.getElementById('edit_end_date').value = end_date ? end_date : '';
            document.getElementById('editTaskModal').style.display = 'block';
        }
    </script>
<?php
};


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
}
add_shortcode('display_tasks', 'display_tasks_shortcode');


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
