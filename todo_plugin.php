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