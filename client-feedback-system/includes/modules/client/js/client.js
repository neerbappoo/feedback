(function ($) {
  "use strict";

  $(document).ready(function () {
    console.log("loaded from client.js");

    // client management

    var editDialog = document.getElementById("edit-client-dialog");
    var editForm = document.getElementById("edit-client-form");

    // Open edit dialog
    $(".edit-client").on("click", function (e) {
      e.preventDefault();
      var clientId = $(this).data("client-id");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_get_client_data",
          nonce: $('#edit-client-form input[name="nonce"]').val(),
          client_id: clientId,
        },
        success: function (response) {
          if (response.success) {
            var client = response.data;
            editForm.reset();
            $('#edit-client-form input[name="client_id"]').val(client.id);
            $("#edit-name").val(client.name);
            $("#edit-company").val(client.company);
            $("#edit-email").val(client.email);
            $("#edit-phone").val(client.phone);
            $("#edit-feedback").val(client.feedback);

            // Reset checkboxes and selects
            $('input[name="entry_points[]"]').prop("checked", false);
            $('select[name^="contact_mode"]').val("Email");
            $('select[name^="feedback_model"]').val("Simple Rating scale");

            // Set entry points and feedback data
            if (client.entry_points) {
              client.entry_points.forEach(function (point) {
                $("#edit-" + point).prop("checked", true);
                if (client.feedback_data && client.feedback_data[point]) {
                  $('select[name="contact_mode[' + point + ']"]').val(
                    client.feedback_data[point].contact_mode
                  );
                  populateFeedbackModel(
                    point,
                    client.feedback_data[point].contact_mode
                  );
                }
              });
            }

            editDialog.showModal();
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    // Close edit dialog
    $("#edit-cancel").on("click", function () {
      editDialog.close();
    });

    // Submit edit form
    $("#edit-client-form").on("submit", function (e) {
      e.preventDefault();
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: $(this).serialize(),
        success: function (response) {
          if (response.success) {
            alert("Client updated successfully.");
            editDialog.close();
            location.reload(); // Reload the page to show updated data
          } else {
            alert("Error: " + response.data.message);
          }
        },
      });
    });

    $("#feedback").on("change", function () {
      $(".feedback-options").toggle($(this).val() === "Yes");
    });
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function () {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.display === "block") {
          panel.style.display = "none";
        } else {
          panel.style.display = "block";
        }
      });
    }

    $('input[type="checkbox"][name="entry_points[]"]').on(
      "change",
      function () {
        var point = $(this).val();
        var selectContactMode = $('select[name="contact_mode[' + point + ']"]');
        var selectFeedbackModel = $(
          'select[name="feedback_model[' + point + ']"]'
        );

        if ($(this).is(":checked")) {
          selectContactMode.prop("disabled", false);
          selectFeedbackModel.prop("disabled", true);

          // If there's only one option, select it and populate feedback model
          if (selectContactMode.find("option").length === 1) {
            selectContactMode.val(
              selectContactMode.find("option").first().val()
            );
            populateFeedbackModel(point, selectContactMode.val());
          } else {
            // If there are multiple options, trigger change event to populate feedback model
            selectContactMode.trigger("change");
          }
        } else {
          selectContactMode.prop("disabled", true);
          selectFeedbackModel.prop("disabled", true);
        }
      }
    );

    $('select[name^="contact_mode"]').on("change", function () {
      var point = $(this).data("point");
      var selectedMode = $(this).val();
      populateFeedbackModel(point, selectedMode);
    });

    function populateFeedbackModel(point, modeId, selectedModel) {
      var selectFeedbackModel = $(
        'select[name="feedback_model[' + point + ']"]'
      );
      selectFeedbackModel.empty();
      selectFeedbackModel.prop("disabled", true);

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_get_feedback_forms",
          mode_id: modeId,
        },
        success: function (response) {
          if (response.success) {
            var forms = response.data;
            forms.forEach(function (form) {
              selectFeedbackModel.append(
                $("<option>", {
                  value: form.ID,
                  text: form.post_title,
                })
              );
            });
            selectFeedbackModel.prop("disabled", false);
            if (selectedModel) {
              selectFeedbackModel.val(selectedModel);
            } else {
              // Select the first option by default
              selectFeedbackModel.val(
                selectFeedbackModel.find("option:first").val()
              );
            }
          } else {
            console.error("Error: " + response.data);
          }
        },
      });
    }

    // Handle form submission
    $("#client-form").on("submit", function (e) {
      e.preventDefault();
      var formData = $(this).serialize();

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: formData,
        success: function (response) {
          if (response.success) {
            alert(response.data.message);
            location.reload(); // Reload the page to reflect the new client
          } else {
            alert("Error: " + response.data.message);
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred: " + error);
        },
      });
    });

    // Delete client
    $(".delete-client").on("click", function (e) {
      e.preventDefault();
      var clientId = $(this).data("client-id");
      var nonce = $(this).data("nonce");
      var confirmMessage = $(this).data("confirm-message");

      if (confirm(confirmMessage)) {
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "cfs_delete_client",
            client_id: clientId,
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
  });
})(jQuery);
