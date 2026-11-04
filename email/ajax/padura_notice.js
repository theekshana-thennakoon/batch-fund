$(function () {
  $(document).on("submit", ".padura-email-form", function (e) {
    e.preventDefault();

    const form = this;
    const $form = $(form);
    const $btn = $form.find("button[type='submit']");
    const isBulk = $form.find("input[name='sendBulkPaduraStatus']").length > 0;
    const resetText = $btn.data("resetText") || "Send Notice";
    const processingText = $btn.data("processingText") || "Sending Emails...";

    function proceedSending() {
      showSending($btn, processingText);

      $.ajax({
        url: "../email/models/padura_notice.php",
        type: "POST",
        data: new FormData(form),
        contentType: false,
        cache: false,
        processData: false,
        dataType: "json",
        success: function (res) {
          hideSending($btn, resetText);
          const status = res.status;
          const msg = res.msg;

          if (status === "correct") {
            Swal.fire({
              title: "Success! 🪕",
              text: msg,
              icon: "success",
              customClass: {
                confirmButton: "btn btn-primary w-xs mt-2",
              },
              buttonsStyling: false,
              showCloseButton: true,
            });
          } else {
            Swal.fire({
              title: "Oops...",
              text: msg,
              icon: "error",
              customClass: {
                confirmButton: "btn btn-danger w-xs mt-2",
              },
              buttonsStyling: false,
              showCloseButton: true,
            });
          }
        },
        error: function (xhr) {
          hideSending($btn, resetText);
          let errorMsg = "Unable to send email. Please check your network and try again.";

          if (xhr.responseJSON && xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          } else if (xhr.responseText) {
            try {
              const parsed = JSON.parse(xhr.responseText);
              if (parsed.msg) {
                errorMsg = parsed.msg;
              }
            } catch (err) {}
          }

          Swal.fire({
            title: "Error Sending Notice",
            text: errorMsg,
            icon: "error",
            customClass: {
              confirmButton: "btn btn-danger w-xs mt-2",
            },
            buttonsStyling: false,
            showCloseButton: true,
          });
        },
      });
    }

    if (isBulk) {
      Swal.fire({
        title: "Send Notice to ALL Students?",
        text: "This will broadcast the official පැදුර 2026 (Padura Musical Night) invitation to all registered batch members.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Yes, Send to All!',
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "btn btn-danger me-2",
          cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
      }).then((result) => {
        if (result.isConfirmed) {
          proceedSending();
        }
      });
    } else {
      proceedSending();
    }
  });

  function showSending($button, text) {
    $button.prop("disabled", true);
    $button.addClass("disabled");
    const $spinner = $button.find(".btn-spinner");
    const $text = $button.find(".btn-text");

    if ($spinner.length) {
      $spinner
        .addClass("spinner-border spinner-border-sm me-2")
        .attr("role", "status")
        .show();
    }
    if ($text.length) {
      $text.text(text);
    } else {
      $button.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + text);
    }
  }

  function hideSending($button, originalText) {
    $button.prop("disabled", false);
    $button.removeClass("disabled");
    const $spinner = $button.find(".btn-spinner");
    const $text = $button.find(".btn-text");

    if ($spinner.length) {
      $spinner.removeClass("spinner-border spinner-border-sm me-2").removeAttr("role").hide();
    }
    if ($text.length) {
      $text.text(originalText);
    } else {
      $button.text(originalText);
    }
  }
});

