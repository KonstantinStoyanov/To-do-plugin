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


// Define plugin constants
define('TODO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TODO_PLUGIN_URL', plugin_dir_url(__FILE__));


// Include necessary files
include_once TODO_PLUGIN_DIR . 'includes/task-database.php';
include_once TODO_PLUGIN_DIR . 'includes/task-admin.php';
include_once TODO_PLUGIN_DIR . 'includes/task-shortcode.php';
include_once TODO_PLUGIN_DIR . 'includes/task-ajax.php';

// Enqueue scripts and styles
function task_manager_enqueue_scripts()
{
    wp_enqueue_style('task-manager', TODO_PLUGIN_URL . 'assets/css/todo-plugin.css');
    wp_enqueue_script('task-manager', TODO_PLUGIN_URL . 'assets/js/todo-plugin.js', array('jquery'), null, true);
    wp_localize_script('task-manager', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'task_manager_enqueue_scripts');
add_action('admin_enqueue_scripts', 'task_manager_enqueue_scripts');



function enqueue_datepicker_scripts()
{
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}
add_action('wp_enqueue_scripts', 'enqueue_datepicker_scripts');
add_action('admin_enqueue_scripts', 'enqueue_datepicker_scripts');




function task_manager_deactivate()
{
    // Deactivation logic here (if needed)
}
register_deactivation_hook(__FILE__, 'task_manager_deactivate');
