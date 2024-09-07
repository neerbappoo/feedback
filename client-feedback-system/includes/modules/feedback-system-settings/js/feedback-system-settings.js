jQuery(document).ready(function ($) {
  console.log("Feedback System Settings JS loaded");
  const dialog = document.getElementById("crud-dialog");
  const form = document.getElementById("crud-form");
  const saveButton = document.getElementById("crud-save");
  const cancelButton = document.getElementById("crud-cancel");

  // Open modal for adding new item
  $(".add-cpt").on("click", function () {
    const postType = $(this).data("type");
    openModal("create", postType);
  });

  // Open modal for editing item
  $(".edit-cpt").on("click", function () {
    const postId = $(this).data("id");
    const postType = $(this).data("type");
    openModal("update", postType, postId);
  });

  // Handle delete button click
  $(".delete-cpt").on("click", function () {
    if (confirm("Are you sure you want to delete this item?")) {
      const postId = $(this).data("id");
      const postType = $(this).data("type");
      crudOperation("delete", postType, postId);
    }
  });

  // Handle form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const action = document.getElementById("crud-action").value;
    const postType = document.getElementById("crud-post-type").value;
    const postId = document.getElementById("crud-post-id").value;
    crudOperation(action, postType, postId);
  });

  // Close modal when cancel button is clicked
  cancelButton.addEventListener("click", function () {
    dialog.close();
  });

  function openModal(action, postType, postId = null) {
    document.getElementById("crud-action").value = action;
    document.getElementById("crud-post-type").value = postType;
    document.getElementById("crud-post-id").value = postId || "";
    document.getElementById("crud-title").value = "";

    // Reset select fields
    $("#associated-modes").val([]);
    $("#preferred-forms").val([]);

    // Show/hide relevant fields based on post type
    $("#entry-point-modes").toggle(postType === "entry_point");
    $("#contact-mode-forms").toggle(postType === "contact_mode");

    if (action === "update" && postId) {
      // Fetch existing data for editing
      crudOperation("read", postType, postId);
    }

    dialog.showModal();
  }

  function crudOperation(action, postType, postId = null) {
    const data = {
      action: "crud_feedback_cpt",
      cfs_feedback_settings_nonce: $("#cfs_feedback_settings_nonce").val(),
      crud_action: action,
      post_type: postType,
      post_id: postId,
      title: document.getElementById("crud-title").value,
    };

    if (postType === "entry_point") {
      data.associated_modes = $("#associated-modes").val();
    } else if (postType === "contact_mode") {
      data.preferred_forms = $("#preferred-forms").val();
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: data,
      success: function (response) {
        if (response.success) {
          if (action === "read") {
            document.getElementById("crud-title").value = response.data.title;
            if (postType === "entry_point") {
              $("#associated-modes").val(response.data.associated_modes);
            } else if (postType === "contact_mode") {
              $("#preferred-forms").val(response.data.preferred_forms);
            }
          } else {
            // Refresh the page to show updated data
            location.reload();
          }
        } else {
          alert("Operation failed. Please try again.");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
        alert("An error occurred. Please try again.");
      },
    });

    if (action !== "read") {
      dialog.close();
    }
  }
});
