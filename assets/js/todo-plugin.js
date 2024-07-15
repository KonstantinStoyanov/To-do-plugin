jQuery(document).ready(function ($) {
  console.log("test");
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
});
