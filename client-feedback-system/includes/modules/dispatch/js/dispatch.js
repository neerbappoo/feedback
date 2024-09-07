(function ($) {
  "use strict";

  $(document).ready(function () {
    function loadClients(companyId, contactModeId) {
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_filter_clients",
          company_id: companyId,
          contact_mode_id: contactModeId,
        },
        success: function (response) {
          if (response.success) {
            var clients = response.data;
            var tableBody = $("#client-feedback-table-body");
            tableBody.empty();
            clients.forEach(function (client) {
              var contactModes = client.contact_modes
                .map(function (mode) {
                  return mode.id + " - " + mode.name; // Line 14: Include both ID and name
                })
                .join(", ");
              var feedbackModels = client.feedback_models
                .map(function (model) {
                  return (
                    "<a href='" +
                    model.link +
                    "'>" +
                    model.id +
                    " - " +
                    model.name +
                    "</a>"
                  ); // Line 16-18: Include both ID and name
                })
                .join(", ");

              var row =
                "<tr>" +
                "<td><input type='checkbox' name='client_ids[]' value='" +
                client.ID +
                "'></td>" +
                "<td>" +
                client.ID +
                "</td>" +
                "<td>" +
                client.name +
                "</td>" +
                "<td>" +
                client.email +
                "</td>" +
                "<td>" +
                client.phone +
                "</td>" +
                "<td>" +
                client.company +
                "</td>" +
                "<td>" +
                contactModes +
                "</td>" +
                "<td>" +
                feedbackModels +
                "</td>" +
                "<td><button type='button' class='send-feedback-btn' data-client-id='" +
                client.ID +
                "'>Send</button></td>" +
                "</tr>";
              tableBody.append(row);
            });
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    }

    $("#filter-clients").on("click", function () {
      var companyId = $("#company_id").val();
      var contactModeId = $("#contact_mode_id").val();
      loadClients(companyId, contactModeId);
    });

    $("#select-all-clients").on("change", function () {
      var isChecked = $(this).is(":checked");
      $("input[name='client_ids[]']").prop("checked", isChecked);
    });

    $("#send-feedback").on("click", function () {
      var clientIds = [];
      $("input[name='client_ids[]']:checked").each(function () {
        clientIds.push($(this).val());
      });

      if (clientIds.length === 0) {
        alert("No clients selected.");
        return;
      }

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_send_feedback",
          client_ids: clientIds,
        },
        success: function (response) {
          if (response.success) {
            alert(response.data);
            loadClients($("#company_id").val(), $("#contact_mode_id").val());
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    $(".send-feedback-btn").on("click", function () {
      var clientId = $(this).data("client-id");
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "cfs_send_feedback",
          client_ids: [clientId],
        },
        success: function (response) {
          if (response.success) {
            alert(response.data);
            loadClients($("#company_id").val(), $("#contact_mode_id").val());
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    // Initial load of clients
    loadClients($("#company_id").val(), $("#contact_mode_id").val());
  });
})(jQuery);
