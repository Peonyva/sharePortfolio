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
  // Save Button
  $("#AddWorkExp").on("submit", function (e) {
    e.preventDefault();

    if (!validateWorkExpForm(this)) return;

    const isCurrent = $("#isCurrent").is(":checked");
    const endDate = $("#endDate").val();

    if (!isCurrent && !endDate) {
      showError(
        "Incomplete information.",
        "Please select an End Date if 'Current' is not selected."
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
      `<div class="container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`
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
                              <label for="companyName" class="required-label">Company Name :</label>
                              <input type="text" id="companyName" name="companyName" value="${
                                data.companyName
                              }">
                          </div>
                          <div class="form-group">
                              <label for="employeeType" class="required-label">Employment Type :</label>
                              <select id="employeeType" name="employeeType" class="form-select" value="${
                                data.employeeType
                              }">
                                  <option value="Full-time">Full-time</option>
                                  <option value="Part-time">Part-time</option>
                                  <option value="Contract">Contract</option>
                                  <option value="Freelance">Freelance</option>
                                  <option value="Internship">Internship</option>
                              </select>
                          </div>
                      </div>

                      <div class="grid grid-cols-3">
                          <div class="form-group">
                              <label for="position" class="required-label">Position :</label>
                              <input type="text" id="position" name="position" value="${
                                data.position
                              }">
                          </div>

                          <div class="form-group">
                              <label for="startDate" class="required-label">Start Date :</label>
                              <input type="date" id="startDate" name="startDate" value="${
                                data.startDate
                              }">
                          </div>
                          <div class="form-group">
                              <label for="endDate" class="required-label">End Date :</label>
                              <input type="date" id="endDate" name="endDate" value="${
                                data.endDate || ""
                              }" ${data.isCurrent == 1 ? "disabled" : ""}>
                              <div class="error-message">End date must be after start date.</div>
                          </div>
                      </div>

                      <div class="form-checkbox-group">
                          <input type="checkbox" id="isCurrent" name="isCurrent" class="form-checkbox" ${
                            data.isCurrent == 1 ? "checked" : ""
                          }>>
                          <label for="isCurrent">I currently work here</label>
                      </div>

                      <div class="form-group">
                          <label for="jobDescription" class="required-label">Job Description :</label>
                          <textarea id="jobDescription" name="jobDescription">${
                            data.jobDescription
                          }</textarea>
                          <div class="description-message">Press Enter to separate each item onto a new line.</div>
                      </div>

                      <div class="form-group">
                          <label for="remarks">Remarks :</label>
                          <textarea id="remarks" name="remarks">${
                            data.remarks || ""
                          }</textarea>
                      </div>
                        
                    </div> `);

    $("#WorkExp").append(container);
    container.append(workItem);

    // Handle move up/down
    container.find(".move-up-btn").click(function () {
      let currentSort = parseInt($(this).data("current-sort"));
      moveWorkItem(itemId, currentSort, currentSort - 1);
    });

    container.find(".move-down-btn").click(function () {
      let currentSort = parseInt($(this).data("current-sort"));
      moveWorkItem(itemId, currentSort, currentSort + 1);
    });

    // Handle checkbox change in edit mode
    container.on("change", `.isCurrent-${itemId}`, function () {
      const endDateInput = container.find(`.endDate-${itemId}`);
      if ($(this).is(":checked")) {
        endDateInput.val("").prop("disabled", true);
      } else {
        endDateInput.prop("disabled", false);
      }
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
      url: "move-work.php",
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
          loadAllWorkItems($("#userID").val()); // โหลดข้อมูลใหม่หลัง swap
        } else {
          console.error("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      },
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
