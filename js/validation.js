/* ================= SCRIPTS ================= */


// defines contact form validation: name, email, message
$(function () {
  $.validator.addMethod("validName", function (value, element) {
    // if there are extra spaces, invalid
    if (/^\s|\s$/.test(value)) return false;
    const trimmed = $.trim(value);

    // if less than 2 characters, invalid
    if (trimmed.length < 2) return false;
    return this.optional(element) || /^[A-Za-z]+(?: [A-Za-z]+)*$/.test(trimmed);
  }, "Please enter a valid name (letters only, no extra spaces).");

  //email
  $.validator.addMethod("validCustomEmail", function (value, element) {
    // if there are extra spaces, invalid
    if (/^\s|\s$/.test(value)) return false;
    value = $.trim(value);

    // allowed characters: letters, numbers, '_', '.', '@', '-'
    if (!/^[A-Za-z0-9._@-]+$/.test(value)) return false;

    // must contain exactly one '@' and end with .com
    if ((value.match(/@/g) || []).length !== 1) return false;
    if (!/\.com$/i.test(value)) return false;

    // split into local and domain fpr checking (full domain includes the .com)
    const parts = value.split("@");
    if (parts.length !== 2) return false;
    const localPart = parts[0]; // only one @ is allowed
    const domainFull = parts[1];            // e.g. "xyz.com" or "xyz.com.com"
    const domainTrimmed = domainFull.replace(/\.com$/i, ""); // remove final .com

    // both sides must have at least one valid character
    if (!localPart || !domainTrimmed) return false;

    // domain must only contain valid domain characters and not start/end with '.' or '-'
    if (!/^[A-Za-z0-9.-]+$/.test(domainTrimmed)) return false;
    if (/^\./.test(domainTrimmed) || /\.$/.test(domainTrimmed)) return false;
    if (/^\-/.test(domainTrimmed) || /\-$/.test(domainTrimmed)) return false;
    if (/\.\./.test(domainTrimmed)) return false; // no consecutive dots

    // invalid ".com.com" / any ".com." inside domainFull
    if (/\.com\./i.test(domainFull)) return false;

    // domain (trimmed) must contain at least one letter (rejects if both numerix)
    if (!/[A-Za-z]/.test(domainTrimmed)) return false;

    // should not have consecutive dots
    if (/^\./.test(localPart) || /\.$/.test(localPart) || /\.\./.test(localPart)) return false;

    // mwessage if invalid
    return true;
  }, "Email may contain letters, numbers, and _@.- only; must include '@' and end with '.com'.");

// applies contact form validation
  $("#contactForm").validate({
    onkeyup: function (element) { $(element).valid(); },
    onfocusout: function (element) { $(element).valid(); },

    rules: {
      name: {
        required: true,
        validName: true
      },
      email: {
        required: true,
        validCustomEmail: true
      },
      message: {
        required: true,
        minlength: 10,
        normalizer: function (value) { return $.trim(value); }
      }
    },

    messages: {
      name: { required: "Please enter your name." },
      email: {
        required: "Please enter your email address.",
        validCustomEmail: "Please enter a valid email (letters, numbers, '_''@''.''-' and must end with .com)."
      },
      message: {
        required: "Please enter your message.",
        minlength: "Your message must be at least 10 characters long."
      }
    },

    errorElement: "div",
    errorClass: "text-danger small",
    errorPlacement: function (error, element) {
      $(error).hide().insertAfter(element).fadeIn(120);
    },
    highlight: function (element) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element) {
      $(element).removeClass("is-invalid").addClass("is-valid");
    },

    submitHandler: function (form) {
      const $msg = $('<div class="alert alert-success mt-3" role="alert">Message successfully sent!</div>');
      $msg.hide().insertBefore(form).fadeIn(180).delay(1400).fadeOut(300, function() { $(this).remove(); });
      form.reset();
      $(form).find('.is-valid').removeClass('is-valid');
    }
  });
});


$(function () {
  // defines validation rules (for login & signup)
  // helper: render inline feedback inside modal forms
  function showFeedback(containerId, type, message) {
    const $box = $('#' + containerId);
    if (!$box.length) return;
    const klass = type === 'success' ? 'alert-success' : type === 'warning' ? 'alert-warning' : 'alert-danger';
    const html = '<div class="alert ' + klass + ' py-2 px-3 mb-0" role="alert">' + message + '</div>';
    $box.stop(true, true).hide().html(html).fadeIn(140);
  }
  $.validator.addMethod("validName", function (value, element) {
    if (/^\s|\s$/.test(value)) return false;
    const trimmed = $.trim(value);
    if (trimmed.length < 2) return false;
    return this.optional(element) || /^[A-Za-z]+(?: [A-Za-z]+)*$/.test(trimmed);
  }, "Please enter a valid name (letters only, no extra spaces).");

  $.validator.addMethod("validCustomEmail", function (value, element) {
    // if there are extra spaces, invalid
    if (/^\s|\s$/.test(value)) return false;
    value = $.trim(value);

    // allowed characters: letters, numbers, '_', '.', '@', '-'
    if (!/^[A-Za-z0-9._@-]+$/.test(value)) return false;

    // must contain exactly one '@' and end with .com
    if ((value.match(/@/g) || []).length !== 1) return false;
    if (!/\.com$/i.test(value)) return false;

    // split into local and domain (full domain includes the .com)
    const parts = value.split("@");
    if (parts.length !== 2) return false;
    const localPart = parts[0]; // only one @ is allowed
    const domainFull = parts[1];            // e.g. "xyz.com" or "xyz.com.com"
    const domainTrimmed = domainFull.replace(/\.com$/i, ""); // remove final .com

    // both sides must have at least one valid character
    if (!localPart || !domainTrimmed) return false;

    // domain must only contain valid domain characters and not start/end with '.' or '-'
    if (!/^[A-Za-z0-9.-]+$/.test(domainTrimmed)) return false;
    if (/^\./.test(domainTrimmed) || /\.$/.test(domainTrimmed)) return false;
    if (/^\-/.test(domainTrimmed) || /\-$/.test(domainTrimmed)) return false;
    if (/\.\./.test(domainTrimmed)) return false; // no consecutive dots

    // invalid ".com.com" / any ".com." inside domainFull
    if (/\.com\./i.test(domainFull)) return false;

    // domain (trimmed) must contain at least one letter (rejects if both numerix)
    if (!/[A-Za-z]/.test(domainTrimmed)) return false;

    // should not have consecutive dots
    if (/^\./.test(localPart) || /\.$/.test(localPart) || /\.\./.test(localPart)) return false;

    // mwessage if invalid
    return true;
  }, "Email may contain letters, numbers, and _@.- only; must include '@' and end with '.com'.");

  //contact number: must start with 9 and have 10 digits
  $.validator.addMethod("validContact", function (value, element) {
    return /^9\d{9}$/.test(value);
  }, "Enter a 10-digit number starting with 9.");

  //password: at least 8 characters, one uppercase, one number
  $.validator.addMethod("strongPassword", function (value, element) {
    return /^(?=.*[A-Z])(?=.*\d).{8,}$/.test(value);
  }, "At least 8 characters, one uppercase and one number.");

// applies sign up form validation
  $("#signupForm").validate({
    onkeyup: function (el) { $(el).valid(); },
    onfocusout: function (el) { $(el).valid(); },
    rules: {
      firstName: { required: true, validName: true },
      lastName: { required: true, validName: true },
      signupEmail: { required: true, validCustomEmail: true },
      contactNumber: { required: true, validContact: true },
      signupPassword: { required: true, strongPassword: true },
      confirmPassword: { required: true, equalTo: "#signupPassword" } //password matching logic
    },
    messages: {
      firstName: { required: "Please enter your first name." },
      lastName: { required: "Please enter your last name." },
      signupEmail: { required: "Please enter your email address." },
      contactNumber: { required: "Please enter your contact number." },
      signupPassword: { required: "Please enter your password." },
      confirmPassword: { required: "Please confirm your password.", equalTo: "Passwords do not match." }
    },
    errorElement: "div",
    errorClass: "text-danger small",
    errorPlacement: function (error, element) {
      //positioning of error messages
    const container = element.closest('.position-relative, .input-group');
    if (container.length) {
      error.insertAfter(container);
    } else {
      error.insertAfter(element);
    }
    $(error).hide().fadeIn(120);
  },
    highlight: function (el) {
      $(el).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (el) {
      $(el).removeClass("is-invalid").addClass("is-valid");
    },
    // submit handler for signup form
    submitHandler: function (form) {
      $.ajax({
        type: "POST",
        url: "/website-popmart/db/signup_process.php", // backend file to handle insert
        data: $(form).serialize(),
        success: function (response) {
          const res = response.trim();
          if (res === "success") {
            showFeedback('signupFeedback', 'success', 'Account created successfully! Please login.');
            form.reset();
            $(form).find('.is-valid').removeClass('is-valid');
            setTimeout(function(){ $('#signupModal').modal('hide'); $('#loginModal').modal('show'); }, 900);
          } else if (res === 'duplicate_email') {
            showFeedback('signupFeedback', 'danger', 'This email is already registered. Please log in instead.');
          } else {
            showFeedback('signupFeedback', 'danger', 'Signup failed. ' + response);
          }
        },
        error: function (xhr, status, error) {
          showFeedback('signupFeedback', 'danger', 'Network error. Please try again.');
        }
      });
    }
  });

  // login form validation
  $("#loginForm").validate({
    onkeyup: function (el) { $(el).valid(); },
    onfocusout: function (el) { $(el).valid(); },
    rules: {
      loginEmail: { required: true, validCustomEmail: true },
      loginPassword: { required: true }
    },
    messages: {
      loginEmail: { required: "Please enter your email." },
      loginPassword: { required: "Please enter your password." }
    },
    errorElement: "div",
    errorClass: "text-danger small",
    errorPlacement: function (error, element) {
    // positioning of error messages
    const container = element.closest('.position-relative, .input-group');
    if (container.length) {
      error.insertAfter(container);
    } else {
      error.insertAfter(element);
    }
    $(error).hide().fadeIn(120);
  },
    highlight: function (el) {
      $(el).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (el) {
      $(el).removeClass("is-invalid").addClass("is-valid");
    },
    submitHandler: function (form) {
      $.ajax({
        type: "POST",
        url: "/website-popmart/db/login_process.php",
        data: $(form).serialize(),
            success: function (response) {
              const res = response.trim();
              if (res === "success_admin") {
                showFeedback('loginFeedback', 'success', 'Login successful! Redirecting...');
                setTimeout(function(){ window.location.href = "/website-popmart/admin/dashboard.php"; }, 700);
              } else if (res === "success_customer" || res === "success") {
                showFeedback('loginFeedback', 'success', 'Login successful! Redirecting...');
                setTimeout(function(){ location.reload(); }, 700);
              } else if (res === "invalid_password") {
                showFeedback('loginFeedback', 'danger', 'Wrong password. Please try again.');
              } else if (res === "no_user") {
                showFeedback('loginFeedback', 'warning', "No account yet. Please sign up to log in.");
              } else {
                showFeedback('loginFeedback', 'danger', 'Login failed. ' + response);
              }
            },
        error: function (xhr, status, error) {
          showFeedback('loginFeedback', 'danger', 'Network error. Please try again.');
        }
      });
    }
  });

  // password toggle
  $(document).on('click', '.toggle-password', function () {
    const input = $(this).closest('.position-relative').find('input');
    const type = input.attr('type') === 'password' ? 'text' : 'password';
    input.attr('type', type);
    $(this).toggleClass('bi-eye bi-eye-slash');
  });

  //contact num filter (for signup)
  $('#contactNumber').on('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
    $(this).valid();
  });
});