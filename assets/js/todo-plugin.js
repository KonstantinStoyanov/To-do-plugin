jQuery(document).ready(function ($) {
  // Add custom admin scripts here
  $("#editTaskModal .close").on("click", function () {
    $("#editTaskModal").hide();
  });

  // Datepicker initialization
  $(".datepicker").datepicker({
    dateFormat: "dd mm yy", // Set the date format to dd mm yyyy
    changeMonth: true,
    changeYear: true,
    yearRange: "2020:2030", // Optional: Set year range as needed
  });

  $(".task-state-checkbox").change(function () {
    var taskId = $(this).data("task-id");
    var newState = $(this).is(":checked") ? 1 : 0;

    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "change_task_state",
        task_id: taskId,
        state: newState,
      },
      success: function (response) {
        alert("Task state updated successfully!");
      },
      error: function () {
        alert("Error updating task state.");
      },
    });
  });
});
