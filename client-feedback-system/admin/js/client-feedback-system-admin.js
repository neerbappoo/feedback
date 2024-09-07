(function ($) {
  "use strict";

  $(document).ready(function () {
    console.log("Hello Admin from Client Feedback System");

    // Bulk actions
    $("#bulk-actions").on("change", function () {
      var action = $(this).val();
      if (action === "assign_page") {
        $("#assign-page-dropdown").show();
      } else {
        $("#assign-page-dropdown").hide();
      }
    });
    $("#apply-bulk-action").on("click", function () {
      var selectedClients = [];
      $('input[name="client_ids[]"]:checked').each(function () {
        selectedClients.push($(this).val());
      });

      var action = $("#bulk-actions").val();
      var pageId = $("#assigned-page").val();

      if (action === "assign_page" && selectedClients.length > 0 && pageId) {
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "cfs_bulk_assign_page",
            client_ids: selectedClients,
            page_id: pageId,
            nonce: $("#cfs-bulk-actions-nonce").val(),
          },
          success: function (response) {
            if (response.success) {
              alert(response.data);
              location.reload();
            } else {
              alert("Error: " + response.data);
            }
          },
          error: function (jqXHR, textStatus, errorThrown) {
            alert("An error occurred: " + textStatus + " - " + errorThrown);
          },
        });
      } else {
        alert("Please select at least one client and a page to assign.");
      }
    });
    $("#select-all-clients").on("change", function () {
      $('input[name="client_ids[]"]').prop("checked", $(this).prop("checked"));
    });

    // Edit form
    $(".edit-form").on("click", function (e) {
      e.preventDefault();
      var formId = $(this).data("form-id");
      var formName = $(this).data("form-name");
      var formJsonEncoded = $(this).attr("data-form-json");

      // Decode the JSON string
      var formJson = JSON.parse(decodeURIComponent(formJsonEncoded));
      console.log(formJson);
      // Populate the edit form
      $("#edit-form-id").val(formId);
      $("#edit-form-name").val(formName);
      $("#edit-form-json").val(formJson);

      var dialog = document.getElementById("edit-form-form");
      dialog.showModal();
    });
    $("#close-edit-form-form").on("click", function (e) {
      e.preventDefault();
      var dialog = document.getElementById("edit-form-form");
      dialog.close();
    });
    // Delete form
    $(".delete-form").on("click", function (e) {
      e.preventDefault();
      var formId = $(this).data("form-id");
      var nonce = $(this).data("nonce");
      var confirmMessage = $(this).data("confirm-message");
      if (confirm(confirmMessage)) {
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "cfs_delete_form",
            form_id: formId,
            nonce: nonce,
          },
          success: function (response) {
            if (response.success) {
              location.reload();
            } else {
              alert(response.data);
            }
          },
        });
      }
    });
    // Update form
    $("#edit-form-form form").on("submit", function (e) {
      e.preventDefault();
      var form = $(this);
      var modalContent = $("#modal-content");
      var errorElement = modalContent.find(".error");
      var successElement = modalContent.find(".success");
      var debugElement = modalContent.find(".debug");

      // Clear previous messages
      errorElement.text("");
      successElement.text("");
      debugElement.text("");
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function (response) {
          if (response.success) {
            successElement.text("Success: " + response.data);
            location.reload();
          } else {
            errorElement.text(
              "Error: " + (response.data || "Unknown error occurred")
            );
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("AJAX Error:", textStatus, errorThrown);
          errorElement.text(
            "An error occurred: " + textStatus + " - " + errorThrown
          );
          debugElement.text("Error details: " + jqXHR.responseText);
        },
      });
    });

    // View form results

    $(".view-form-results").on("click", function (e) {
      e.preventDefault();
      var formTitle = $(this).data("form-title");
      $("#current-form-title").text(formTitle);
      $("#form-list").hide();
      $("#leaderboard").hide();
      $("#form-results").show();

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_get_form_results",
          form_title: formTitle,
        },
        success: function (response) {
          if (response.success) {
            $("#form-results-body").html(response.data);
          } else {
            alert("Error loading form results");
          }
        },
      });
    });

    $("#back-to-forms").on("click", function (e) {
      e.preventDefault();
      $("#form-results").hide();
      $("#form-list").show();
      $("#leaderboard").show();
    });
  });
})(jQuery);
