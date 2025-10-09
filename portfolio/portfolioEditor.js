// ============================================
// 🔹 ฟังก์ชันแสดง Error ด้วย SweetAlert2
// ============================================
async function showError(title, text) {
  return await Swal.fire({
    icon: "error",
    title: title,
    text: text,
    confirmButtonText: "Confirmed",
    confirmButtonColor: "#ef4444",
  });
}

// ============================================
// 🔹 ฟังก์ชันแสดง Toast (แจ้งเตือนมุมขวาบน)
// ============================================
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

$(document).ready(function () {
  // =============================
  // 🔹 ปุ่ม Toggle สำหรับทุกส่วน
  // =============================
  $(".btn-toggle").click(function () {
    const target = $(this).data("target");
    $(target).toggleClass("hidden");
  });

  // =============================
  // 🔹 Work Experience
  // =============================

  $("#btnCancelWorkExp").click(function () {
    $("#AddWorkExp").addClass("hidden");
    $("#AddWorkExp")[0].reset();
  });

  $("#AddWorkExp").on("submit", function (e) {
    e.preventDefault();

    if (!validateWorkExpForm(this)) {
      return;
    }

    const isCurrent = $("#isCurrent").is(":checked");
    const endDate = $("#endDate").val();

    if (!isCurrent && !endDate) {
      showError(
        "Incomplete information.",
        " Please select an End Date if 'Current' is not selected."
      );
      return;
    }

    const formData = new FormData(this);
    formData.append("userID", $("#userID").val());

    $.ajax({
      url: "/portfolio/workExperince/insertExp.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function () {
        if (response.status === 1) {
          showToast("Work Experience saved!");
          $("#AddWorkExp").addClass("hidden");
          $("#AddWorkExp")[0].reset();
          loadWorkExp();
        } else {
          showError(
            "An error occurred",
            response.message || "Please try again."
          );
        }
      },
      error: function () {
        showError(
          "An error has occurred",
          "The Work Experience could not be saved"
        );
      },
    });

    // =============================
    // 🔹 Education
    // =============================

    $("#btnCancelEducation").click(function () {
      $("#AddEducation").addClass("hidden");
      $("#AddEducation")[0].reset();
    });

    $("#AddEducation").on("submit", function (e) {
      e.preventDefault();

      if (!validateEducationForm(this)) {
        return;
      }

      const formData = new FormData(this);
      formData.append("userID", $("#userID").val());

      $.ajax({
        url: "/portfolio/education/insertEducation.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function () {
          if (response.status === 1) {
            showToast("Education saved!");
            $("#AddEducation").addClass("hidden");
            $("#AddEducation")[0].reset();
            loadEducation();
          } else {
            showError(
              "An error occurred",
              response.message || "Please try again."
            );
          }
        },
        error: function () {
          showError(
            "An error has occurred",
            "The Education could not be saved"
          );
        },
      });
    });

    // =============================
    // 🔹 Project
    // =============================

    $("#btnCancelProject").click(function () {
      $("#AddProject").addClass("hidden");
      $("#AddProject")[0].reset();
    });

    $("#AddProject").on("submit", function (e) {
      e.preventDefault();

      if (!validateProjectForm(this)) {
        return;
      }

      const formData = new FormData(this);
      formData.append("userID", $("#userID").val());
      formData.append("myProjectSkills", $("#myProjectSkillsInput").val());

      $.ajax({
        url: "/portfolio/project/insertProject.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function () {
          if (response.status === 1) {
            showToast("Project saved!");
            $("#AddProject").addClass("hidden");
            $("#AddProject")[0].reset();
            loadProjects();
          } else {
            showError(
              "An error occurred",
              response.message || "Please try again."
            );
          }
        },
        error: function () {
          showError(
            "An error has occurred",
            "The Education could not be saved"
          );
        },
      });
    });

    // =============================
    // 🔹 Validation
    // =============================

    function validateWorkExpForm(form) {
      const company = $(form).find("#companyName").val();
      const position = $(form).find("#position").val();
      const start = $(form).find("#startDate").val();
      const end = $(form).find("#endDate").val();
      const isCurrent = $(form).find("#isCurrent").is(":checked");

      if (!company || !position || !start) {
        showError(
          "Incomplete Information",
          "Please fill out all required fields."
        );
        return false;
      }

      if (!isCurrent && end && new Date(end) < new Date(start)) {
        showError("Invalid Date", "End Date must be after Start Date.");
        return false;
      }

      return true;
    }

    function validateEducationForm(form) {
      const name = $(form).find("#educationName").val();
      const degree = $(form).find("#degree").val();
      const start = $(form).find("#startDate").val();
      const end = $(form).find("#endDate").val();
      const isCurrent = $(form).find("#isCurrent").is(":checked");

      if (!name || !degree || !start) {
        showError(
          "Incomplete Information",
          "Please fill out all required fields."
        );
        return false;
      }

      if (!isCurrent && end && new Date(end) < new Date(start)) {
        showError("Invalid Date", "End Date must be after Start Date.");
        return false;
      }

      return true;
    }

    function validateProjectForm(form) {
      const title = $(form).find("#projectTitle").val();
      const desc = $(form).find("#keyPoint").val();

      if (!title || !desc) {
        showError(
          "Incomplete Information",
          "Please fill out all required fields."
        );
        return false;
      }

      return true;
    }
  });
});
