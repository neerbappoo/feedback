(function ($) {
  "use strict";

  $(document).ready(function () {
    console.log("loaded from company.js");

    // Delete company
    $(".delete-company").on("click", function (e) {
      e.preventDefault();
      var companyId = $(this).data("company-id");
      var nonce = $(this).data("nonce");
      var confirmMessage = $(this).data("confirm-message");

      if (confirm(confirmMessage)) {
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "cfs_delete_company",
            company_id: companyId,
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
    // Edit company
    $(".edit-company").on("click", function (e) {
      e.preventDefault();
      var companyId = $(this).data("company-id");
      var row = $(this).closest("tr");
      $("#edit-company-id").val(companyId);
      $("#edit-company-name").val(row.find("td:eq(2)").text());
      $("#edit-company-authorized").val(
        row.find("td:eq(3)").text().toLowerCase()
      );
      var dialog = document.getElementById("edit-company-form");
      dialog.showModal();
    });
    $("#close-edit-company-form").on("click", function (e) {
      e.preventDefault();
      var dialog = document.getElementById("edit-company-form");
      dialog.close();
    });

    $("#edit-company-form form").on("submit", function (e) {
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

    // Filter companies by authorized status
    $("#filter-authorized").on("change", function () {
      var url = new URL(window.location.href);
      url.searchParams.set("filter_authorized", $(this).val());
      window.location.href = url.toString();
    });

    // Filter clients by company
    $("#filter-company").on("change", function () {
      var url = new URL(window.location.href);
      url.searchParams.set("filter_company", $(this).val());
      window.location.href = url.toString();
    });
    $("#bulk-action-form").on("submit", function (e) {
      e.preventDefault();
      var action = $("#bulk-actions").val();
      var selectedCompanies = [];

      $('input[name="company_ids[]"]:checked').each(function () {
        selectedCompanies.push($(this).val());
      });

      if (action === "trash" && selectedCompanies.length > 0) {
        if (confirm("Are you sure you want to trash the selected companies?")) {
          $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "cfs_bulk_trash_companies",
              company_ids: selectedCompanies,
              nonce: $("#cfs_bulk_action_nonce").val(),
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
        }
      } else {
        alert("Please select at least one company and choose an action.");
      }
    });
    $("#select-all-companies").on("change", function () {
      $('input[name="company_ids[]"]').prop("checked", $(this).prop("checked"));
    });
  });
})(jQuery);
