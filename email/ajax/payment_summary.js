$(function () {
  $(document).on("submit", ".payment-summary-form", function () {
    const $form = $(this);
    const $btn = $form.find("button[type='submit']");
    const resetText = $btn.data("resetText") || "Send To All Students";
    const processingText = $btn.data("processingText") || "Processing...";

    showSending($btn, processingText);

    $.ajax({
      url: "../email/models/payment_summary.php",
      type: "POST",
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
      dataType: "json",
      success: function (html) {
        var status = html.status;
        var msg = html.msg;

        if (status == "correct") {
          hideSending($btn, resetText);
          Swal.fire({
            title: "Success!",
            text: msg,
            icon: "success",
            customClass: {
              confirmButton: "btn btn-primary w-xs mt-2",
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(() => {
            window.location.reload();
          });
        } else {
          hideSending($btn, resetText);
          Swal.fire({
            title: "Oops...",
            text: msg,
            icon: "error",
            customClass: {
              confirmButton: "btn btn-primary w-xs mt-2",
            },
            buttonsStyling: false,
            showCloseButton: true,
          });
        }
      },
      error: function (xhr, status, error) {
        hideSending($btn, resetText);
        let errorMsg = "Unable to send email. Please try again.";

        // Try to extract error reason from server response
        if (xhr.responseJSON && xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
        } else if (xhr.responseText) {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.msg) {
              errorMsg = response.msg;
            }
          } catch (e) {
            // Response is not JSON, use default
          }
        }

        Swal.fire({
          title: "Oops...",
          text: errorMsg,
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary w-xs mt-2",
          },
          buttonsStyling: false,
          showCloseButton: true,
        });
      },
    });

    return false;
  });
});

function showSending($btn, text = "Processing...") {
  const $btnText = $btn.find(".btn-text");
  const $spinner = $btn.find(".btn-spinner");

  $btnText.text(text);
  $spinner
    .html(
      `
        <div class="spinner-border spinner-border-sm text-light" role="status">
            <span class="visually-hidden">Processing...</span>
        </div>
    `
    )
    .show();
  $btn.prop("disabled", true);
}

function hideSending($btn, text = "Send To All Students") {
  const $btnText = $btn.find(".btn-text");
  const $spinner = $btn.find(".btn-spinner");

  $spinner.hide().html("");
  $btnText.text(text);
  $btn.prop("disabled", false);
}
