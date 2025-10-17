// ============================================
// 1️⃣ UTILITY FUNCTIONS (ฟังก์ชันช่วยเหลือ)
// ============================================

// 🔹 Show Error with SweetAlert2
async function showError(title, text) {
  return await Swal.fire({
    icon: "error",
    title: title,
    text: text,
    confirmButtonText: "Confirmed",
    confirmButtonColor: "#ef4444",
  });
}

// 🔹 Show Toast (Alert Top Right)
function showToast(title) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });

  Toast.fire({
    icon: "success",
    title: title,
  });
}

// ============================================
// 2️⃣ VALIDATION FUNCTIONS (ฟังก์ชันตรวจสอบข้อมูล)
// ============================================
// ฟังก์ชันนี้ใช้สำหรับหน้า Register

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

  // ✅ รวมการตรวจสอบเงื่อนไขรหัสผ่านทั้งหมดใน Regex เดียว
  // เงื่อนไข: 8-16 ตัว, มีอักษรตัวเล็ก, ตัวใหญ่, ตัวเลข, และสัญลักษณ์
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

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showError("Validation Error", "Invalid email format.");
    return false;
  }

  if (!password) {
    showError("Validation Error", "Password is required.");
    return false;
  }

  return true;
}

// ============================================
// 3️⃣ GLOBAL FUNCTIONS (ฟังก์ชันที่ใช้ได้ทุกหน้า)
// ============================================

function togglePassword() {

  var clickedIcon = this;

  // หา input ที่อยู่ใกล้ที่สุด (หรือใน div เดียวกัน)
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

// ============================================
// 4️⃣ EVENTS (จัดการเหตุการณ์เมื่อ DOM พร้อม)
// ============================================

$(function () {
  // 🔸 Register Form Submission Event
  $("#register").on("submit", function (e) {
    e.preventDefault();

    if (!validateRegisterForm(this)) return;

    const formData = new FormData(this);

    $.ajax({
      url: "/insert-register.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showToast("Register saved!");
          $("#register")[0].reset();

          // Redirect ไปหน้า Login
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

$("#login").on("submit", function (e) {
  e.preventDefault();

  if (!validateLoginForm(this)) return;

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
        showToast("Login successful!");

        const userData = response.data;
        const userID = userData.userID;
        let redirectURL = '/portfolio/portfolio-editor.php';

        // ✅ เก็บข้อมูลไว้ใน localStorage
        localStorage.setItem("userData", JSON.stringify(userData));

        // ถ้าเคยเผยแพร่แล้ว -> ไปหน้า portfolio.php
        if (userData.isEverPublic === 1) {
          redirectURL = '/portfolio/portfolio.php';
        }

        redirectURL += '?user=' + userID;

        // ✅ ไปหน้าต่อหลังล็อกอิน
        setTimeout(() => {
          window.location.href = redirectURL;
        }, 1500);

      } else {
        showError("An error occurred", response.message || "Please try again.");
      }
    },
    error: function () {
      showError("An error has occurred", "The login could not be processed.");
    },
  });
});




  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', togglePassword);
  });
});