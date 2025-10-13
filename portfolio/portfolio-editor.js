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

  //  Cancel Button
  $("#btnCancelWorkExp").click(function () {
    Swal.fire({
      title: "Confirm cancellation?",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
      confirmButtonColor: "#3b82f6",
      cancelButtonColor: "#ef4444",
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddWorkExp").addClass("hidden");
        $("#AddWorkExp")[0].reset();
        showToast("Work Experience form has been cleared.");
      }
    });
  });

  // Handle checkbox in ADD form
  $("#workIsCurrent").change(function () {
    if ($(this).is(":checked")) {
      $("#workEndDate").val("").prop("disabled", true);
    } else {
      $("#workEndDate").prop("disabled", false);
    }
  });

  // Save Button
  $("#AddWorkExp").on("submit", function (e) {
    e.preventDefault();

    if (!validateWorkExpForm(this)) return;

    const isCurrent = $("#workIsCurrent").is(":checked");
    const endDate = $("#workEndDate").val();

    if (!isCurrent && !endDate) {
      showError(
        "Incomplete information.",
        "Please select an End Date or check 'Currently working here'."
      );
      return;
    }

    const formData = new FormData(this);
    formData.append("userID", $("#userID").val());

    $.ajax({
      url: "/portfolio/workExperience/insert-work.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showToast("Work Experience saved!");
          $("#AddWorkExp").addClass("hidden");
          $("#AddWorkExp")[0].reset();
          $("#workEndDate").prop("disabled", false); // Reset disabled state

          let userID = $("#userID").val();
          loadWorkExp(userID);
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
          "The Work Experience could not be saved."
        );
      },
    });
  });

  // Function load your data from server and display on web.
  function loadWorkExp(userID) {
    $.ajax({
      url: "/portfolio/workExperience/get-work.php",
      type: "GET",
      dataType: "json",
      data: {
        userID: userID,
      },
      cache: false,
      success: function (response) {
        if (response.status === 1) {
          $("#WorkExp").empty();
          response.data.sort(
            (a, b) => parseInt(a.sortOrder) - parseInt(b.sortOrder)
          );
          response.data.forEach(function (item) {
            appendWorkItem(item, response.data);
          });
        } else {
          console.error("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      },
    });
  }

  // Function show your data on web. (sub function loadWorkExp(userID);)
  function appendWorkItem(data, allData) {
    let sortOrder = parseInt(data.sortOrder);
    let itemId = data.id;
    let totalItems = allData.length;

    let container = $(
        `<div class="work-item-container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`
    );

    let upButton = "";
    let downButton = "";

    if (totalItems > 1) {
        if (sortOrder > 1) {
            upButton = `<button type="button" class="btn btn-secondary move-up-btn" data-id="${itemId}" data-current-sort="${sortOrder}">
                <i class="fa-solid fa-arrow-up"></i> Up
            </button>`;
        }
        if (sortOrder < totalItems) {
            downButton = `<button type="button" class="btn btn-secondary move-down-btn" data-id="${itemId}" data-current-sort="${sortOrder}">
                <i class="fa-solid fa-arrow-down"></i> Down
            </button>`;
        }
    }

    let workItem = $(`
        <div class="work-item">
            <div class="controller">
                ${upButton}
                ${downButton}
            </div>
            <div class="item-header">
                <h3 class="item-title">Work Experience ${sortOrder}</h3>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="required-label">Company Name :</label>
                    <input type="text" class="work-company-name" data-id="${itemId}" name="companyName" value="${data.companyName}">
                </div>
                <div class="form-group">
                    <label class="required-label">Employment Type :</label>
                    <select class="work-employee-type form-select" data-id="${itemId}" name="employeeType">
                        <option value="Full-time" ${data.employeeType === "Full-time" ? "selected" : ""}>Full-time</option>
                        <option value="Part-time" ${data.employeeType === "Part-time" ? "selected" : ""}>Part-time</option>
                        <option value="Contract" ${data.employeeType === "Contract" ? "selected" : ""}>Contract</option>
                        <option value="Freelance" ${data.employeeType === "Freelance" ? "selected" : ""}>Freelance</option>
                        <option value="Internship" ${data.employeeType === "Internship" ? "selected" : ""}>Internship</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="required-label">Position :</label>
                    <input type="text" class="work-position" data-id="${itemId}" name="position" value="${data.position}">
                </div>

                <div class="form-group">
                    <label class="required-label">Start Date :</label>
                    <input type="date" class="work-start-date" data-id="${itemId}" name="startDate" value="${data.startDate}">
                </div>
                <div class="form-group">
                    <label class="required-label">End Date :</label>
                    <input type="date" class="work-end-date" data-id="${itemId}" name="endDate" value="${data.endDate || ""}" ${data.isCurrent == 1 ? "disabled" : ""}>
                    <div class="error-message">End date must be after start date.</div>
                </div>
            </div>

            <div class="form-checkbox-group">
                <input type="checkbox" class="work-is-current form-checkbox" data-id="${itemId}" name="isCurrent" ${data.isCurrent == 1 ? "checked" : ""}>
                <label>I currently work here</label>
            </div>

            <div class="form-group">
                <label class="required-label">Job Description :</label>
                <textarea class="work-job-description" data-id="${itemId}" name="jobDescription">${data.jobDescription}</textarea>
                <div class="description-message">Press Enter to separate each item onto a new line.</div>
            </div>

            <div class="form-group">
                <label>Remarks :</label>
                <textarea class="work-remarks" data-id="${itemId}" name="remarks">${data.remarks || ""}</textarea>
            </div>

            <div class="btn-wrapper">
                <button type="button" class="btn btn-success btn-update-work" data-id="${itemId}">Update</button>
                <button type="button" class="btn btn-danger btn-delete-work" data-id="${itemId}">Delete</button>
            </div>
        </div>
    `);

    $("#WorkExp").append(container);
    container.append(workItem);

    // ✅ Handle move up/down
    container.find(".move-up-btn").click(function () {
        let currentSort = parseInt($(this).data("current-sort"));
        moveWorkItem(itemId, currentSort, currentSort - 1);
    });

    container.find(".move-down-btn").click(function () {
        let currentSort = parseInt($(this).data("current-sort"));
        moveWorkItem(itemId, currentSort, currentSort + 1);
    });

    // ✅ Handle checkbox change in edit mode
    container.find(".work-is-current").change(function () {
        const endDateInput = container.find(".work-end-date");
        if ($(this).is(":checked")) {
            endDateInput.val("").prop("disabled", true);
        } else {
            endDateInput.prop("disabled", false);
        }
    });

    // ✅ Handle Update Button
    container.find(".btn-update-work").click(function () {
        updateWorkItem(itemId, container);
    });

    // ✅ Handle Delete Button
    container.find(".btn-delete-work").click(function () {
        deleteWorkItem(itemId, container);
    });
}
  // Move work item up/down
  function moveWorkItem(currentId, currentSort, newSort) {
    console.log("Moving Item:", {
      currentId: currentId,
      currentSort: currentSort,
      newSort: newSort,
      userID: $("#userID").val(),
    });

    $.ajax({
      url: "/portfolio/workExperience/move-work.php",
      type: "POST",
      data: {
        currentId: currentId,
        currentSort: currentSort,
        newSort: newSort,
        userID: $("#userID").val(),
      },
      dataType: "json",
      success: function (response) {
        console.log("Move Response:", response);
        if (response.status === 1) {
          loadWorkExp($("#userID").val()); // โหลดข้อมูลใหม่หลัง swap
        } else {
          console.error("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      },
    });
  }

// ฟังก์ชัน Update Work Experience Item
function updateWorkItem(itemId, container) {
    const companyName = container.find(".work-company-name").val();
    const employeeType = container.find(".work-employee-type").val();
    const position = container.find(".work-position").val();
    const startDate = container.find(".work-start-date").val();
    const endDate = container.find(".work-end-date").val();
    const isCurrent = container.find(".work-is-current").is(":checked");
    const jobDescription = container.find(".work-job-description").val();
    const remarks = container.find(".work-remarks").val();

    // Validation
    if (!isCurrent && endDate && new Date(endDate) < new Date(startDate)) {
        showError("Invalid Date", "End Date must be after Start Date.");
        return;
    }

    if (!isCurrent && endDate && new Date(endDate) > new Date()) {
        showError("Invalid Date", "End Date cannot be in the future.");
        return;
    }

    // ส่งข้อมูลไป update
    $.ajax({
        url: "/portfolio/workExperience/update-work.php",
        method: "POST",
        data: {
            id: itemId,
            companyName: companyName,
            employeeType: employeeType,
            position: position,
            startDate: startDate,
            endDate: endDate,
            isCurrent: isCurrent ? 1 : 0,
            jobDescription: jobDescription,
            remarks: remarks
        },
        dataType: "json",
        success: function (response) {
            if (response.status === 1) {
                showToast("Work Experience updated successfully!");
            } else {
                showError("Update failed", response.message || "Please try again.");
            }
        },
        error: function () {
            showError("An error occurred", "Could not update Work Experience.");
        }
    });
}

// ฟังก์ชัน Delete Work Experience Item
function deleteWorkItem(itemId, container) {
    Swal.fire({
        title: "Confirm deletion?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/portfolio/workExperience/delete-work.php",
                method: "POST",
                data: { id: itemId },
                dataType: "json",
                success: function (response) {
                    if (response.status === 1) {
                        container.remove();
                        showToast("Work Experience deleted successfully!");
                        
                        // Reload เพื่อ update sortOrder
                        loadWorkExp($("#userID").val());
                    } else {
                        showError("Deletion failed", response.message || "Please try again.");
                    }
                },
                error: function () {
                    showError("An error occurred", "Could not delete Work Experience.");
                }
            });
        }
    });
}


  // =============================
  // 🔹 Education
  // =============================
  //  Cancel Button
  $("#btnCancelEducation").click(function () {
    Swal.fire({
      title: "Confirm cancellation?",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
      confirmButtonColor: "#3b82f6",
      cancelButtonColor: "#ef4444",
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddEducation").addClass("hidden");
        $("#AddEducation")[0].reset();
        showToast("Education form has been cleared.");
      }
    });
  });

    $("#eduIsCurrent").change(function () {
        if ($(this).is(":checked")) {
            $("#eduEndDate").val("").prop("disabled", true);
        } else {
            $("#eduEndDate").prop("disabled", false);
        }
    });

  // Save Button
  $("#AddEducation").on("submit", function (e) {
    e.preventDefault();

    if (!validateEducationForm(this)) return;

    const formData = new FormData(this);
    formData.append("userID", $("#userID").val());

    $.ajax({
      url: "/portfolio/education/insert-education.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showToast("Education saved!");
          $("#AddEducation").addClass("hidden");
          $("#AddEducation")[0].reset();

          let userID = $("#userID").val();
          loadEducation(userID);
        } else {
          showError(
            "An error occurred",
            response.message || "Please try again."
          );
        }
      },
      error: function () {
        showError("An error has occurred", "The Education could not be saved.");
      },
    });
  });

  // Function load your data from server and display on web.

  // =============================
  // 🔹 Project
  // =============================
  //  Cancel Button
  $("#btnCancelProject").click(function () {
    Swal.fire({
      title: "Confirm cancellation?",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
      confirmButtonColor: "#3b82f6",
      cancelButtonColor: "#ef4444",
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddProject").addClass("hidden");
        $("#AddProject")[0].reset();
        showToast("Project form has been cleared.");
      }
    });
  });

  // Save Button
  $("#AddProject").on("submit", function (e) {
    e.preventDefault();

    if (!validateProjectForm(this)) return;

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
      success: function (response) {
        if (response.status === 1) {
          showToast("Project saved!");
          $("#AddProject").addClass("hidden");
          $("#AddProject")[0].reset();

          let userID = $("#userID").val();
          loadProjects(userID);
        } else {
          showError(
            "An error occurred",
            response.message || "Please try again."
          );
        }
      },
      error: function () {
        showError("An error has occurred", "The Project could not be saved.");
      },
    });
  });
  // Function load your data from server and display on web.

  // =============================
  // 🔹 Validation
  // =============================
  function validateWorkExpForm(form) {
    const start = $(form).find("#workStartDate").val();
    const end = $(form).find("#workEndDate").val();
    const isCurrent = $(form).find("#workIsCurrent").is(":checked");

    if (!isCurrent && end && new Date(end) < new Date(start)) {
      showError("Invalid Date", "End Date must be after Start Date.");
      return false;
    }

    if (!isCurrent && end && new Date(end) > new Date()) {
      showError("Invalid Date", "End Date cannot be in the future.");
      return false;
    }

    return true;
  }

  function validateEducationForm(form) {
    const start = $("#eduStartDate").val();
    const end = $("#eduEndDate").val();
    const isCurrent = $("#eduIsCurrent").is(":checked");

    if (!isCurrent && end && new Date(end) < new Date(start)) {
      showError("Invalid Date", "End Date must be after Start Date.");
      return false;
    }

    if (!isCurrent && end && new Date(end) > new Date()) {
      showError("Invalid Date", "End Date cannot be in the future.");
      return false;
    }

    return true;
  }
});
