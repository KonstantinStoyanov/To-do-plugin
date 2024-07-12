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

function task_manager_scripts()
{
    // Enqueue the styles and scripts for the front end
    wp_enqueue_style('task-manager', plugins_url('/assets/css/todo-plugin.css', __FILE__));
    wp_enqueue_script('task-manager', plugins_url('/assets/js/todo-plugin.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'task_manager_scripts');

function task_manager_admin_scripts()
{
    // Enqueue the styles and scripts for the admin area
    wp_enqueue_style('task-manager-admin', plugins_url('/assets/css/todo-plugin.css', __FILE__));
    wp_enqueue_script('task-manager-admin', plugins_url('/assets/js/todo-plugin.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'task_manager_admin_scripts');


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
    echo "task_manager_page";
}
