$(function () {
  //  news latter Form
  let news_latter = $("form.news_latter").jbvalidator({
    errorMessage: true,
    successClass: false,
  });

  $(document).on("submit", ".news_latter", function () {
    const $form = $(this);
    const $btn = $form.find("button[type='submit']");

    showSending($btn);

    $.ajax({
      url: "./email/models/newslatter.php",
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
          hideSending($btn);
          Swal.fire({
            title: "Success!",
            text: msg,
            icon: "success",
            showCancelButton: false,
            customClass: {
              confirmButton: "btn btn-primary w-xs me-2 mt-2",
              popup: "swal2-border-radius",
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then((result) => {
            $(".news_latter")[0].reset();
            window.location = "./";
          });
        } else {
          hideSending($btn);
          Swal.fire({
            title: "Oops...",
            text: msg,
            icon: "error",
            customClass: {
              confirmButton: "btn btn-primary w-xs mt-2",
              popup: "swal2-border-radius",
            },
            buttonsStyling: false,
            showCloseButton: true,
          });
        }
      },
    });

    return false;
  });

  //  Contact Form
  let contact_form = $("form.contact_form").jbvalidator({
    errorMessage: true,
    successClass: false,
  });

  $(document).on("submit", ".contact_form", function () {
    const $form = $(this);
    const $btn = $form.find("button[type='submit']");

    showSending($btn);

    $.ajax({
      url: "./email/models/message.php",
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
          hideSending($btn);
          Swal.fire({
            title: "Success!",
            text: msg,
            icon: "success",
            showCancelButton: false,
            customClass: {
              confirmButton: "btn btn-primary w-xs me-2 mt-2",
              popup: "swal2-border-radius",
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then((result) => {
            $(".contact_form")[0].reset();
            window.location = "contact";
          });
        } else {
          hideSending($btn);
          Swal.fire({
            title: "Oops...",
            text: msg,
            icon: "error",
            customClass: {
              confirmButton: "btn btn-primary w-xs mt-2",
              popup: "swal2-border-radius",
            },
            buttonsStyling: false,
            showCloseButton: true,
          });
        }
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

// Hide sending spinner and restore button text
function hideSending($btn, text = "Subscribe") {
  const $btnText = $btn.find(".btn-text");
  const $spinner = $btn.find(".btn-spinner");

  $spinner.hide().html("");
  $btnText.text(text);
  $btn.prop("disabled", false);
}
