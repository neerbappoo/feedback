(function ($) {
  "use strict";

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(document).ready(function () {
    console.log("Hello World from Client Feedback System");
    if (typeof cfsFormData !== "undefined") {
      //console.log(cfsFormData);
      const survey = new Survey.Model(cfsFormData.formJson);

      survey.onComplete.add(alertResults);

      $("#" + cfsFormData.surveyContainerId).Survey({ model: survey });
    }

    function alertResults(sender) {
      const urlParams = new URLSearchParams(window.location.search);
      let clientId = null;
      for (let param of urlParams) {
        console.log(param);
        if (param[0] === "cid") {
          clientId = param[1];
        }
        // sender.setValue(param[0], param[1]);
      }

      if (clientId === null) {
        console.error("Client ID not found in the URL parameters.");
        return;
      }

      const results = sender.data;
      const { points, ...resultsWithoutPoints } = results;

      console.log("Posting survey results:");
      console.log(results);

      // Save the survey results to the WordPress database
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "cfs_save_survey_results",
          client_id: clientId,
          form_id: cfsFormData.formId,
          form_title: cfsFormData.formTitle,
          points: JSON.stringify(results.points),
          survey_results: JSON.stringify(resultsWithoutPoints),
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            console.log("Survey results saved successfully!");
          } else {
            console.error("Error saving survey results:", response.data);
          }
        },
        error: function (xhr, status, error) {
          console.error("Error saving survey results:", error);
        },
      });
    }
  });
})(jQuery);
