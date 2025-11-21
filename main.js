// 1. UTILITY FUNCTIONS (ฟังก์ชันช่วยเหลือ) 

// Show Error with SweetAlert2

async function showError(title, text) {
  return await Swal.fire({ icon: "error", title: title, text: text, });
}

//  Show Toast (Alert Top Right)
async function showSuccess(title) {
  return await Swal.fire({ icon: "success", title: title, });
}


// 2️. VALIDATION FUNCTIONS (ฟังก์ชันตรวจสอบข้อมูล)

function validateRegisterForm(form) {
  const firstname = $(form).find("#firstname").val().trim();
  const lastname = $(form).find("#lastname").val().trim();
  const birthdate = $(form).find("#birthdate").val().trim();
  const email = $(form).find("#email").val().trim();
  const password = $(form).find("#password").val();
  const confirmPassword = $(form).find("#password-confirm").val();

  if (!firstname) {
    showError("Validation Error", "Firstname is required.");
    return false;
  }

  if (!lastname) {
    showError("Validation Error", "Lastname is required.");
    return false;
  }

  if (!birthdate) {
    showError("Validation Error", "Date of birth is required.");
    return false;
  }

  if (!email) {
    showError("Validation Error", "Email is required.");
    return false;
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showError("Validation Error", "Invalid email format.");
    return false;
  }

  if (!password) {
    showError("Validation Error", "Password is required.");
    return false;
  }

  const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()])[A-Za-z0-9!@#$%^&*()]{8,16}$/;

  if (!passwordPattern.test(password)) {
    showError(
      "Password Error",
      "Password must be 8–16 characters long and include: uppercase letters, lowercase letters, numbers, and symbols."
    );
    return false;
  }

  if (password !== confirmPassword) {
    showError("Password Error", "Passwords do not match.");
    return false;
  }

  return true;
}

function validateLoginForm(form) {
  const email = $(form).find("#email").val().trim();
  const password = $(form).find("#password").val();

  if (!email) {
    showError("Validation Error", "Email is required.");
    return false;
  }

  // const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  // if (!emailPattern.test(email)) {
  //   showError("Validation Error", "Invalid email format.");
  //   return false;
  // }

  if (!password) {
    showError("Validation Error", "Password is required.");
    return false;
  }
  return true;
}

function validatePasswordResetForm(form) {
  const email = $(form).find("#email").val().trim();

  if (!email) {
    showError("Validation Error", "Email is required.");
    return false;
  }

  // const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  // if (!emailPattern.test(email)) {
  //   showError("Validation Error", "Invalid email format.");
  //   return false;
  // }

  return true;
}


// 3️. GLOBAL FUNCTIONS (ฟังก์ชันที่ใช้ได้ทุกหน้า)

function togglePassword() {

  var clickedIcon = this;

  var container = clickedIcon.closest('.password-container');
  var input = container ? container.querySelector('input[type="password"], input[type="text"]') : null;

  if (!input) return;

  if (input.type === 'password') {
    input.type = 'text';
    clickedIcon.classList.remove('fa-eye');
    clickedIcon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    clickedIcon.classList.remove('fa-eye-slash');
    clickedIcon.classList.add('fa-eye');
  }
}


// 4️. EVENTS (จัดการเหตุการณ์เมื่อ DOM พร้อม)

$(function () {
  // Register Form Submission Event
  $("#register").on("submit", function (e) {
    e.preventDefault();

    if (!validateRegisterForm(this)) return;
    const formData = new FormData(this);

    $.ajax({
      url: "/register-insert.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showSuccess("Register saved!");
          $("#register")[0].reset();

          setTimeout(() => {
            window.location.href = '/login.php';
          }, 1500);

        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function () {
        showError("An error has occurred", "The Register could not be saved.");
      },
    });
  });

  // Login Form Submission Event
  $("#login").on("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
      url: "/get-login.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",

      success: function (response) {
        if (response.status === 1) {
          const user = response.data;

          // ตรวจสอบข้อมูล user
          if (!user || !user.userID) {
            showError("Error: user data missing.");
            return;
          }

          showSuccess("Login successful!");

          // ส่ง userID ผ่าน URL - เริ่มต้นที่หน้า editor เสมอ
          let redirectURL = "/portfolio/portfolio-editor.php?user=" + encodeURIComponent(user.userID);

          setTimeout(() => {
            window.location.href = redirectURL;
          }, 1000);

        } else {
          showError("Login failed: " + (response.message || "Invalid username or password."));
        }
      },

      error: function (xhr, status, error) {
        console.error("Login error:", error);
        showError("Error: Cannot connect to the server.");
      },
    });
  });

  // Password Reset Form Submission Event
  $("#password-reset").on("submit", function (e) {
    e.preventDefault();

    if (!validatePasswordResetForm(this)) return;
    const formData = new FormData(this);

    $.ajax({
      url: "/send-password-reset.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showSuccess("Password reset link has been sent to your email!!");

          setTimeout(() => {
            window.location.href = '/password-reset-link.php';
          }, 1500);

        } else {
          showError("Failed to send reset link", response.message || "Please try again.");
        }
      },
      error: function () {
        showError("An error has occurred", "Unable to send password reset link. Please try again later.");
      },
    });
  });


  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', togglePassword);
  });

});