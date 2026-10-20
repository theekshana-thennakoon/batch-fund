$(function () {
  $(document).on("submit", ".batch-photo-email-form", function (e) {
    e.preventDefault();

    const form = this;
    const $form = $(form);
    const $btn = $form.find("button[type='submit']");
    const isBulk = $form.find("input[name='sendBulkBatchPhotoStatus']").length > 0;
    const resetText = $btn.data("resetText") || "Send Notice";
    const processingText = $btn.data("processingText") || "Sending Emails...";

    function proceedSending() {
      showSending($btn, processingText);

      $.ajax({
        url: "../email/models/batch_photo_notice.php",
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
              title: "Success! 📸",
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
        text: "This will send the Batch Photo 2026 announcement email to all registered batch members.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Yes, Send to All!',
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "btn btn-success me-2",
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

    return false;
  });
});

function showSending($btn, text = "Processing...") {
  const $btnText = $btn.find(".btn-text");
  const $spinner = $btn.find(".btn-spinner");

  $btn.data("originalHtml", $btn.html());
  if ($btnText.length) {
    $btnText.text(text);
  }
  if ($spinner.length) {
    $spinner
      .html(
        `
        <div class="spinner-border spinner-border-sm text-light me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
      `
      )
      .show();
  } else {
    $btn.html(
      `<span class="spinner-border spinner-border-sm me-2" role="status"></span> ${text}`
    );
  }
  $btn.prop("disabled", true);
}

function hideSending($btn, resetText = "Send") {
  $btn.prop("disabled", false);
  const orig = $btn.data("originalHtml");
  if (orig) {
    $btn.html(orig);
  } else {
    $btn.text(resetText);
  }
}
