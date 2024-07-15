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
                <th scope="row"><label for="state">State</label></th>
                <td><input type="checkbox" name="state" id="state" value="1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date (d m Y)</label></th>
                <td><input name="end_date" type="text" id="end_date" class="regular-text datepicker" placeholder="dd mm yyyy"></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add Task"></p>
    </form>

    <h2>Tasks</h2>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th class="manage-column">Title</th>
                <th class="manage-column">Description</th>
                <th class="manage-column">Assignee</th>
                <th class="manage-column">State</th>
                <th class="manage-column">End Date</th>
                <th class="manage-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task) { ?>
                <tr>
                    <td class="column-columnname"><?php echo esc_html($task->title); ?></td>
                    <td class="column-columnname"><?php echo esc_html($task->description); ?></td>
                    <td class="column-columnname"><?php echo esc_html(get_userdata($task->user_id)->display_name); ?></td>
                    <td class="column-columnname">
                        <input type="checkbox" class="task-state-checkbox" data-task-id="<?php echo esc_attr($task->id); ?>" <?php checked($task->state, 1); ?>>
                    </td>
                    <td class="column-columnname"><?php echo $task->end_date ? date('d M Y', strtotime($task->end_date)) : ''; ?></td>
                    <td class="column-columnname">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_task" value="1">
                            <input type="hidden" name="id" value="<?php echo esc_attr($task->id); ?>">
                            <button type="submit" class="button button-link-delete">Delete</button>
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
            <button type="button" class="button close ">&times;</button>
        </form>
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