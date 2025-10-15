// ============================================
// 1️⃣ UTILITY FUNCTIONS (ฟังก์ชันช่วยเหลือ)
// ============================================
// ✅ ต้องอยู่บนสุด เพราะฟังก์ชันอื่นจะเรียกใช้

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
// ✅ อยู่ก่อน CRUD Functions เพราะจะถูกเรียกใช้ใน Submit


function validateDateRange(startDate, endDate, isCurrent, labelText = "currently here") {

  if (!startDate) {
    showError("Please fill in the required information", "Please select a start date");
    return false;
  }

  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : null;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (start > today) {
    showError("Invalid date", "Start date cannot be later than today");
    return false;
  }

  if (!isCurrent) {
    if (!endDate) {
      showError("Missing information", `Please select an end date or check 'I ${labelText}'`);
      return false;
    }

    if (end <= start) {
      showError("Invalid date", "End date must be later than start date");
      return false;
    }

    if (end > today) {
      showError(
        "Invalid date",
        `End date cannot be later than today. If you're still ${labelText}, please check 'I ${labelText}'`
      );
      return false;
    }
  }

  return true;
}

function validateWorkExpForm(form) {
  const start = $(form).find("#workStartDate").val();
  const end = $(form).find("#workEndDate").val();
  const isCurrent = $(form).find("#workIsCurrent").is(":checked");

  return validateDateRange(start, end, isCurrent, {
    fieldName: "Date",
    currentCheckboxLabel: "currently work here"
  });
}

function validateEducationForm(form) {
  const start = $("#eduStartDate").val();
  const end = $("#eduEndDate").val();
  const isCurrent = $("#eduIsCurrent").is(":checked");

  return validateDateRange(start, end, isCurrent, {
    fieldName: "Date",
    currentCheckboxLabel: "currently studying here"
  });
}

function validateProjectForm(form) {
  const title = $(form).find("#projectTitle").val();
  const image = $(form).find("#projectImage")[0].files[0];
  const keyPoint = $(form).find("#keyPoint").val();
  const skills = $(form).find("#myProjectSkillsInput").val();

  if (!title.trim()) {
    showError("Validation Error", "Project title is required.");
    return false;
  }

  if (!image) {
    showError("Validation Error", "Project image is required.");
    return false;
  }

  // ตรวจสอบขนาดไฟล์
  const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
  const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

  if (image.size > MAX_FILE_SIZE) {
    showError("File Too Large", "Image size must not exceed 10MB.");
    return false;
  }

  // ตรวจสอบประเภทไฟล์
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  if (!allowedTypes.includes(image.type)) {
    showError("Invalid File Type", "Only JPG, PNG, and GIF images are allowed.");
    return false;
  }

  if (!keyPoint.trim()) {
    showError("Validation Error", "Job description is required.");
    return false;
  }

  if (!skills || skills === "[]" || skills === "") {
    showError("Validation Error", "Please select at least one skill.");
    return false;
  }

  return true;
}

function validateWorkExpUpdate(container) {
  const startDate = container.find(".work-start-date").val();
  const endDate = container.find(".work-end-date").val();
  const isCurrent = container.find(".work-is-current").is(":checked");

  return validateDateRange(startDate, endDate, isCurrent, {
    fieldName: "Date",
    currentCheckboxLabel: "currently work here"
  });
}

function validateEducationUpdate(container) {
  const startDate = container.find(".edu-start-date").val();
  const endDate = container.find(".edu-end-date").val();
  const isCurrent = container.find(".edu-is-current").is(":checked");

  return validateDateRange(startDate, endDate, isCurrent, {
    fieldName: "Date",
    currentCheckboxLabel: "currently studying here"
  });
}

function validateProjectUpdate(container) {
  const title = container.find(".project-title").val().trim();
  const keyPoint = container.find(".project-keypoint").val().trim();
  const skills = container.find(`.project-skills-data[data-id="${container.data('id')}"]`).val();

  if (!title) {
    showError("Validation Error", "Project title is required.");
    return false;
  }

  if (!keyPoint) {
    showError("Validation Error", "Job description is required.");
    return false;
  }

  if (!skills || skills === "[]") {
    showError("Validation Error", "Please select at least one skill.");
    return false;
  }

  return true;
}

// ============================================
// 3️⃣ WORK EXPERIENCE - CRUD FUNCTIONS
// ============================================

function loadWorkExp(userID) {
  $.ajax({
    url: "/portfolio/workExperience/get-work.php",
    type: "GET",
    dataType: "json",
    data: { userID: userID },
    cache: false,
    success: function (response) {
      if (response.status === 1) {
        $("#WorkExp").empty();
        response.data.sort((a, b) => parseInt(a.sortOrder) - parseInt(b.sortOrder));
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

// 📥 LOAD (ดึงข้อมูลจาก Server)
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
      upButton = `<button type="button" class="btn btn-secondary move-up-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
                <i class="fa-solid fa-arrow-up"></i> Up
            </button>`;
    }
    if (sortOrder < totalItems) {
      downButton = `<button type="button" class="btn btn-secondary move-down-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
                <i class="fa-solid fa-arrow-down"></i> Down
            </button>`;
    }
  }

  let workItem = $(`
        <div class="work-item">
        <div class="controller-header"> 
            <div class="controller">
                ${upButton}
                ${downButton}
            </div>
            <div class="item-header">
                <h3 class="item-title">Work Experience ${sortOrder}</h3>
            </div>
          </div>
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="required-label">Company Name :</label>
                    <input type="text" class="work-company-name" data-id="${itemId}" name="companyName" value="${data.companyName
    }">
                </div>
                <div class="form-group">
                    <label class="required-label">Employment Type :</label>
                    <select class="work-employee-type form-select" data-id="${itemId}" name="employeeType">
                        <option value="Full-time" ${data.employeeType === "Full-time" ? "selected" : ""
    }>Full-time</option>
                        <option value="Part-time" ${data.employeeType === "Part-time" ? "selected" : ""
    }>Part-time</option>
                        <option value="Contract" ${data.employeeType === "Contract" ? "selected" : ""
    }>Contract</option>
                        <option value="Freelance" ${data.employeeType === "Freelance" ? "selected" : ""
    }>Freelance</option>
                        <option value="Internship" ${data.employeeType === "Internship" ? "selected" : ""
    }>Internship</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="required-label">Position :</label>
                    <input type="text" class="work-position" data-id="${itemId}" name="position" value="${data.position
    }">
                </div>

                <div class="form-group">
                    <label class="required-label">Start Date :</label>
                    <input type="date" class="work-start-date" data-id="${itemId}" name="startDate" value="${data.startDate
    }">
                </div>
                <div class="form-group">
                    <label class="required-label">End Date :</label>
                    <input type="date" class="work-end-date" data-id="${itemId}" name="endDate" value="${data.endDate || ""
    }" ${data.isCurrent == 1 ? "disabled" : ""}>
                    <div class="error-message">End date must be after start date.</div>
                </div>
            </div>

            <div class="form-checkbox-group">
                <input type="checkbox" class="work-is-current form-checkbox" data-id="${itemId}" name="isCurrent" ${data.isCurrent == 1 ? "checked" : ""
    }>
                <label>I currently work here</label>
            </div>

            <div class="form-group">
                <label class="required-label">Job Description :</label>
                <textarea class="work-job-description" data-id="${itemId}" name="jobDescription">${data.jobDescription
    }</textarea>
                <div class="description-message">Press Enter to separate each item onto a new line.</div>
            </div>

            <div class="form-group">
                <label>Remarks :</label>
                <textarea class="work-remarks" data-id="${itemId}" name="remarks">${data.remarks || ""
    }</textarea>
            </div>

            <div class="btn-wrapper">
                <button type="button" class="btn btn-success btn-update-work btn-manage" data-id="${itemId}">Update</button>
                <button type="button" class="btn btn-danger btn-delete-work btn-manage" data-id="${itemId}">Delete</button>
            </div>
        </div>
    `);

  $("#WorkExp").append(container);
  container.append(workItem);

  //  Handle move up/down
  container.find(".move-up-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveWorkItem(itemId, currentSort, currentSort - 1);
  });

  container.find(".move-down-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveWorkItem(itemId, currentSort, currentSort + 1);
  });

  // Handle checkbox change in edit mode
  container.find(".work-is-current").change(function () {
    const endDateInput = container.find(".work-end-date");
    if ($(this).is(":checked")) {
      endDateInput.val("").prop("disabled", true);
    } else {
      endDateInput.prop("disabled", false);
    }
  });

  // Handle Update Button
  container.find(".btn-update-work").click(function () {
    updateWorkItem(itemId, container);
  });

  // Handle Delete Button
  container.find(".btn-delete-work").click(function () {
    deleteWorkItem(itemId, container);
  });
  // ✅ จะเรียกใช้ updateWorkItem และ deleteWorkItem
}

// ✏️ UPDATE
function updateWorkItem(itemId, container) {
  // เพิ่ม validation ก่อน update
  const userID = $("#userID").val();
  if (!validateWorkExpUpdate(container)) { return; }

  const companyName = container.find(".work-company-name").val();
  const employeeType = container.find(".work-employee-type").val();
  const position = container.find(".work-position").val();
  const startDate = container.find(".work-start-date").val();
  const endDate = container.find(".work-end-date").val();
  const isCurrent = container.find(".work-is-current").is(":checked");
  const jobDescription = container.find(".work-job-description").val();
  const remarks = container.find(".work-remarks").val();

  $.ajax({
    url: "/portfolio/workExperience/update-work.php",
    method: "POST",
    data: {
      id: itemId,
      userID: userID,
      companyName: companyName,
      employeeType: employeeType,
      position: position,
      startDate: startDate,
      endDate: endDate,
      isCurrent: isCurrent ? 1 : 0,
      jobDescription: jobDescription,
      remarks: remarks,
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
    },
  });
}

// 🗑️ DELETE
function deleteWorkItem(itemId, container) {
  const userID = $("#userID").val(); // ✅ ดึงค่า userID จาก input ที่มี id="userID"

  Swal.fire({
    title: "Confirm deletion?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/portfolio/workExperience/delete-work.php",
        method: "POST",
        data: {
          id: itemId,
          userID: userID, // ✅ ส่งค่า userID ไปด้วย
        },
        dataType: "json",
        success: function (response) {
          if (response.status === 1) {
            showToast("Work Experience deleted successfully!");

            // ✅ Reload ข้อมูลใหม่ของ user เดียวกัน
            loadWorkExp(userID);
          } else {
            showError(
              "Deletion failed",
              response.message || "Please try again."
            );
          }
        },
        error: function () {
          showError(
            "An error occurred",
            "Could not delete Work Experience."
          );
        },
      });
    }
  });
}

// 🔄 MOVE (Up/Down)
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

// ============================================
// 4️⃣ EDUCATION - CRUD FUNCTIONS
// ============================================

function loadEducation(userID) {
  $.ajax({
    url: "/portfolio/education/get-education.php",
    type: "GET",
    dataType: "json",
    data: { userID: userID },
    cache: false,
    success: function (response) {
      if (response.status === 1) {
        $("#Education").empty();
        response.data.sort((a, b) => parseInt(a.sortOrder) - parseInt(b.sortOrder));
        response.data.forEach(function (item) {
          appendEducationItem(item, response.data);
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

function appendEducationItem(data, allData) {
  let sortOrder = parseInt(data.sortOrder);
  let itemId = data.id;
  let totalItems = allData.length;

  let container = $(
    `<div class="education-item-container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`
  );

  let upButton = "";
  let downButton = "";

  if (totalItems > 1) {
    if (sortOrder > 1) {
      upButton = `<button type="button" class="btn btn-secondary move-up-edu-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
        <i class="fa-solid fa-arrow-up"></i> Up
      </button>`;
    }
    if (sortOrder < totalItems) {
      downButton = `<button type="button" class="btn btn-secondary move-down-edu-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
        <i class="fa-solid fa-arrow-down"></i> Down
      </button>`;
    }
  }

  let educationItem = $(`
    <div class="education-item">
      <div class="controller-header"> 
        <div class="controller">
          ${upButton}
          ${downButton}
        </div>
        <div class="item-header">
          <h3 class="item-title">Education ${sortOrder}</h3>
        </div>
      </div>

      <div class="grid grid-cols-2">
        <div class="form-group">
          <label class="required-label">Education Name :</label>
          <input type="text" class="edu-name" data-id="${itemId}" value="${data.educationName}">
        </div>
        <div class="form-group">
          <label class="required-label">Degree :</label>
          <input type="text" class="edu-degree" data-id="${itemId}" value="${data.degree}">
        </div>
        <div class="form-group">
          <label class="required-label">Faculty :</label>
          <input type="text" class="edu-faculty" data-id="${itemId}" value="${data.facultyName}">
        </div>
        <div class="form-group">
          <label class="required-label">Major :</label>
          <input type="text" class="edu-major" data-id="${itemId}" value="${data.majorName}">
        </div>
        <div class="form-group">
          <label class="required-label">Start Date :</label>
          <input type="date" class="edu-start-date" data-id="${itemId}" value="${data.startDate}">
        </div>
        <div class="form-group">
          <label class="required-label">End Date :</label>
          <input type="date" class="edu-end-date" data-id="${itemId}" value="${data.endDate || ""}" ${data.isCurrent == 1 ? "disabled" : ""}>
          <div class="error-message">End date must be after start date.</div>
        </div>
      </div>

      <div class="form-checkbox-group">
        <input type="checkbox" class="edu-is-current form-checkbox" data-id="${itemId}" ${data.isCurrent == 1 ? "checked" : ""}>
        <label>Currently studying here</label>
      </div>

      <div class="form-group">
        <label>Remarks :</label>
        <textarea class="edu-remarks" data-id="${itemId}">${data.remarks || ""}</textarea>
      </div>

      <div class="btn-wrapper">
        <button type="button" class="btn btn-success btn-update-edu btn-manage" data-id="${itemId}">Update</button>
        <button type="button" class="btn btn-danger btn-delete-edu btn-manage" data-id="${itemId}">Delete</button>
      </div>
    </div>
  `);

  $("#Education").append(container);
  container.append(educationItem);

  // =============================
  // Event Handlers
  // =============================

  // ปุ่ม Move Up
  container.find(".move-up-edu-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveEducationItem(itemId, currentSort, currentSort - 1);
  });

  // ปุ่ม Move Down
  container.find(".move-down-edu-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveEducationItem(itemId, currentSort, currentSort + 1);
  });

  // Checkbox handler
  container.find(".edu-is-current").change(function () {
    const endDateInput = container.find(".edu-end-date");
    if ($(this).is(":checked")) {
      endDateInput.val("").prop("disabled", true);
    } else {
      endDateInput.prop("disabled", false);
    }
  });

  // ปุ่ม Update
  container.find(".btn-update-edu").click(function () {
    updateEducationItem(itemId, container);
  });

  // ปุ่ม Delete
  container.find(".btn-delete-edu").click(function () {
    deleteEducationItem(itemId, container);
  });
}


function updateEducationItem(itemId, container) {
  // Validation ก่อน
  if (!validateEducationUpdate(container)) {
    return;
  }

  const educationName = container.find(".edu-name").val().trim();
  const degree = container.find(".edu-degree").val().trim();
  const facultyName = container.find(".edu-faculty").val().trim();
  const majorName = container.find(".edu-major").val().trim();
  const startDate = container.find(".edu-start-date").val();
  const endDate = container.find(".edu-end-date").val();
  const isCurrent = container.find(".edu-is-current").is(":checked");
  const remarks = container.find(".edu-remarks").val().trim();

  // Validation เพิ่มเติม
  if (!educationName || !degree || !facultyName || !majorName || !startDate) {
    showError("Validation Error", "Please fill in all required fields.");
    return;
  }

  $.ajax({
    url: "/portfolio/education/update-education.php",
    method: "POST",
    data: {
      id: itemId,
      userID: $("#userID").val(),
      educationName: educationName,
      degree: degree,
      facultyName: facultyName,
      majorName: majorName,
      startDate: startDate,
      endDate: endDate,
      isCurrent: isCurrent ? 1 : 0,
      remarks: remarks,
    },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        showToast("Education updated successfully!");
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function () {
      showError("An error occurred", "Could not update Education.");
    },
  });
}

function deleteEducationItem(itemId, container) {
  const userID = $("#userID").val();

  Swal.fire({
    title: "Confirm deletion?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/portfolio/education/delete-education.php",
        method: "POST",
        data: {
          id: itemId,
          userID: userID, // ✅ ส่งค่า userID ไปด้วย
        },
        dataType: "json",
        success: function (response) {
          if (response.status === 1) {
            showToast("Education deleted successfully!");

            // ✅ Reload ข้อมูลใหม่ของ user เดียวกัน
            loadEducation(userID);
          } else {
            showError(
              "Deletion failed",
              response.message || "Please try again."
            );
          }
        },
        error: function () {
          showError(
            "An error occurred",
            "Could not delete Education."
          );
        },
      });
    }
  });
}

function moveEducationItem(currentId, currentSort, newSort) {
  $.ajax({
    url: "/portfolio/education/move-education.php",
    type: "POST",
    data: {
      currentId: currentId,
      currentSort: currentSort,
      newSort: newSort,
      userID: $("#userID").val(),
    },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        loadEducation($("#userID").val());
      } else {
        console.error("Error: " + response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
    },
  });
}

// ============================================
// 5️⃣ PROJECT - CRUD FUNCTIONS
// ============================================

function loadProject(userID) {
  $.ajax({
    url: "/portfolio/project/get-project.php",
    type: "GET",
    dataType: "json",
    data: { userID: userID },
    cache: false,
    success: function (response) {
      if (response.status === 1) {
        $("#Project").empty();
        response.data.sort((a, b) => parseInt(a.sortOrder) - parseInt(b.sortOrder));
        response.data.forEach(function (item) {
          appendProjectItem(item, response.data);
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

// ============================================
// 🔧 แก้ไขปัญหา Project CRUD
// ============================================

function appendProjectItem(data, allData) {
  let sortOrder = parseInt(data.sortOrder);
  let itemId = String(data.projectID).trim();
  let totalItems = allData.length;

  console.log("📦 Appending Project Item:", {
    itemId: itemId,
    itemIdType: typeof itemId,
    sortOrder: sortOrder
  });

  let container = $(`
    <div class="project-item-container" 
         data-id="${itemId}" 
         data-sort-order="${sortOrder}">
    </div>`
  );

  // Up/Down Buttons
  let upButton = sortOrder > 1 ? `
    <button type="button" 
            class="btn btn-secondary move-up-project-btn btn-manage" 
            data-id="${itemId}" 
            data-current-sort="${sortOrder}">
      <i class="fa-solid fa-arrow-up"></i> Up
    </button>` : "";

  let downButton = sortOrder < totalItems ? `
    <button type="button" 
            class="btn btn-secondary move-down-project-btn btn-manage" 
            data-id="${itemId}" 
            data-current-sort="${sortOrder}">
      <i class="fa-solid fa-arrow-down"></i> Down
    </button>` : "";

  // =============================
  // Parse Skills
  // =============================
  let skillsArray = [];
  if (Array.isArray(data.skills)) {
    skillsArray = data.skills;
  } else if (typeof data.skills === "string" && data.skills.trim()) {
    let skillsStr = data.skills.trim();
    if (skillsStr.startsWith("[") && skillsStr.endsWith("]")) {
      try {
        skillsArray = JSON.parse(skillsStr);
      } catch (e) {
        console.warn("JSON parse failed, using comma split:", e);
        skillsArray = skillsStr.replace(/[\[\]"]/g, "").split(",").map(s => s.trim()).filter(s => s);
      }
    } else {
      skillsArray = skillsStr.split(",").map(s => s.trim()).filter(s => s);
    }
  }

  const skillsHTML = skillsArray.length
    ? skillsArray.map(skill => `<span class="skill-tag">${skill}</span>`).join("")
    : `<span class="text-muted">No skills selected</span>`;

  // =============================
  // Build Project Item HTML
  // =============================
  let projectItem = $(`
    <div class="project-item" data-project-id="${itemId}">
      <div class="controller-header"> 
        <div class="controller">
          ${upButton}
          ${downButton}
        </div>
        <div class="item-header">
          <h3 class="item-title">Project ${sortOrder}</h3>
        </div>
      </div>

      <div class="form-group">
        <label class="required-label">Project Title :</label>
        <input type="text" 
               class="project-title" 
               data-id="${itemId}" 
               value="${data.projectTitle}">
      </div>

      <div class="form-group">
        <label class="required-label">Project Image :</label>
        <div class="project-image-preview image-preview" data-id="${itemId}">
          <img src="${data.projectImage}" alt="${data.projectTitle}">
        </div>
        <div class="preview-actions">
          <button type="button" 
                  class="btn btn-primary btn-change-image btn-preview-image" 
                  data-id="${itemId}">Change Image
          </button>
          <input type="file" 
                 class="project-image-input hidden" 
                 data-id="${itemId}" 
                 accept="image/*">
        </div>
      </div>

      <div class="form-group">
        <label class="required-label">Job Description :</label>
        <textarea class="project-keypoint" 
                  data-id="${itemId}" 
                  rows="3">${data.keyPoint}</textarea>
        <div class="description-message">Press Enter to separate each item onto a new line.</div>
      </div>

      <div class="form-group">
        <label class="required-label">Skills :</label>
        <div class="skills-list project-skills-display" data-id="${itemId}">
          ${skillsHTML}
        </div>
      </div>

      <div class="project-skills-editor hidden" data-id="${itemId}">
       <div class="skill-editor-container">
        <div class="input-group">
          <div class="form-group dropdown">
            <label>Select Skill :</label>
            <select class="project-skill-dropdown form-select" data-id="${itemId}">
              <option value="">Choose a skill...</option>
            </select>
          </div>
          <div class="btn-wrapper">
            <button type="button" class="btn-add-project-skill btn btn-success btn-manage" data-id="${itemId}" disabled>
              Add Skill
            </button>
          </div>
        </div>

        <div class="selected-skills-box" data-id="${itemId}">
          <h5>Selected Skills (<span class="project-skill-count" data-id="${itemId}">0</span>)</h5>
          <div class="project-skills-list" data-id="${itemId}"></div>
        </div>
        <input type="hidden" class="project-skills-data" data-id="${itemId}" value='${JSON.stringify(skillsArray)}'>
         </div>
      </div>

      <div class="btn-wrapper">
        <button type="button" 
                class="btn-edit-skills btn btn-primary btn-manage" 
                data-id="${itemId}">
          Edit Skills
        </button>

        <button type="button" 
                class="btn-update-project btn btn-success btn-manage" 
                data-id="${itemId}">
          Update
        </button>

        <button type="button" 
                class="btn-delete-project btn btn-danger btn-manage" 
                data-id="${itemId}">
          Delete
        </button>
      </div>
    </div>
  `);

  $("#Project").append(container);
  container.append(projectItem);

  loadSkillsForProjectEdit(itemId);
  initializeProjectSkills(itemId, skillsArray);

  // =============================
  // Event Handlers
  // =============================
 container.find(".move-up-project-btn").click(function () {
    moveProjectItem(itemId, parseInt($(this).data("current-sort")), parseInt($(this).data("current-sort")) - 1);
  });

  container.find(".move-down-project-btn").click(function () {
    moveProjectItem(itemId, parseInt($(this).data("current-sort")), parseInt($(this).data("current-sort")) + 1);
  });

  container.find(".btn-change-image").click(function () {
    container.find(`.project-image-input[data-id="${itemId}"]`).click();
  });

  container.find(`.project-image-input[data-id="${itemId}"]`).change(function () {
    handleProjectImageChange(this, itemId);
  });

  container.find(".btn-edit-skills").click(function () {
    container.find(`.project-skills-editor[data-id="${itemId}"]`).toggleClass("hidden");
  });

  container.find(`.project-skill-dropdown[data-id="${itemId}"]`).change(function () {
    const btn = container.find(`.btn-add-project-skill[data-id="${itemId}"]`);
    btn.prop("disabled", !$(this).val());
  });

  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).click(function () {
    addProjectSkillToEdit(itemId, container);
  });

  container.find(".btn-update-project").click(function () {
    updateProjectItem(itemId, container);
  });

  container.find(".btn-delete-project").click(function () {
    deleteProjectItem(itemId, container);
  });
}

function updateProjectItem(itemId, container) {
  console.group("🔄 UPDATE PROJECT");
  console.log("itemId:", itemId, typeof itemId);
  console.groupEnd();

  if (!itemId || String(itemId).trim() === "") {
    showError("Error", "Project ID is missing.");
    return;
  }

  const title = container.find(".project-title").val().trim();
  const keyPoint = container.find(".project-keypoint").val().trim();
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`).val();
  const imageInput = container.find(`.project-image-input[data-id="${itemId}"]`)[0];

  if (!title) {
    showError("Validation Error", "Project title is required.");
    return;
  }

  if (!keyPoint) {
    showError("Validation Error", "Job description is required.");
    return;
  }

  if (!skillsData || skillsData === "[]") {
    showError("Validation Error", "Please select at least one skill.");
    return;
  }

  const formData = new FormData();
  formData.append("id", String(itemId).trim());
  formData.append("userID", $("#userID").val());
  formData.append("projectTitle", title);
  formData.append("keyPoint", keyPoint);
  formData.append("myProjectSkills", skillsData);

  if (imageInput && imageInput.files.length > 0) {
    formData.append("projectImage", imageInput.files[0]);
  }

  $.ajax({
    url: "/portfolio/project/update-project.php",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      console.log("✅ UPDATE SUCCESS:", response);
      if (response.status === 1) {
        showToast("Project updated successfully!");
        loadProject($("#userID").val());
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function (xhr, status, error) {
      console.error("❌ UPDATE ERROR:", { status, error, response: xhr.responseText });
      showError("An error occurred", "Could not update Project.");
    },
  });
}

function deleteProjectItem(itemId, container) {
  const userID = $("#userID").val();

  console.group("🗑️ DELETE PROJECT");
  console.log("itemId:", itemId, typeof itemId);
  console.log("userID:", userID, typeof userID);
  console.groupEnd();

  if (!itemId || String(itemId).trim() === "") {
    showError("Error", "Project ID is missing.");
    return;
  }

  if (!userID || String(userID).trim() === "") {
    showError("Error", "User ID is missing.");
    return;
  }

  const safeItemId = String(itemId).trim();
  const safeUserID = String(userID).trim();

  Swal.fire({
    title: "Confirm deletion?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append("id", safeItemId);
      formData.append("userID", safeUserID);

      $.ajax({
        url: "/portfolio/project/delete-project.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
          console.log("✅ DELETE SUCCESS:", response);
          if (response.status === 1) {
            showToast("Project deleted successfully!");
            loadProject(safeUserID);
          } else {
            showError("Deletion failed", response.message || "Please try again.");
          }
        },
        error: function (xhr, status, error) {
          console.error("❌ DELETE ERROR:", { status, error, response: xhr.responseText });
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            showError("An error occurred", errorResponse.message || "Could not delete Project.");
          } catch (e) {
            showError("An error occurred", "Could not delete Project.");
          }
        },
      });
    }
  });
}

function moveProjectItem(currentId, currentSort, newSort) {
  $.ajax({
    url: "/portfolio/project/move-project.php",
    type: "POST",
    data: {
      currentId: currentId,
      currentSort: currentSort,
      newSort: newSort,
      userID: $("#userID").val(),
    },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        loadProject($("#userID").val());
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
// 🔹 PROJECT - HELPER FUNCTIONS  ⬅️ ใส่ตรงนี้
// =============================

function loadSkillsForProjectEdit(itemId) {
  $.ajax({
    url: "/portfolio/get-skills.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      let skillsData = [];
      
      // ✅ รองรับทั้ง format ที่มี wrapper และไม่มี
      if (response.status === 1 && Array.isArray(response.data)) {
        skillsData = response.data;
      } else if (Array.isArray(response)) {
        skillsData = response;
      }

      const dropdown = $(`.project-skill-dropdown[data-id="${itemId}"]`);
      dropdown.empty().append('<option value="">Choose a skill...</option>');

      skillsData.forEach(function (skill) {
        const skillName = skill.name || skill.skillName;
        dropdown.append(`<option value="${skillName}">${skillName}</option>`);
      });
    },
    error: function () {
      console.error("Failed to load skills for project edit");
    },
  });
}

function initializeProjectSkills(itemId, skillsArray) {
  const container = $(`.project-item-container[data-id="${itemId}"]`);
  const skillsList = container.find(`.project-skills-list[data-id="${itemId}"]`);
  const skillCount = container.find(`.project-skill-count[data-id="${itemId}"]`);

  if (!skillsList.length) {
    console.warn(`Skills list not found for itemId=${itemId}`);
    return;
  }

  skillsList.empty();

  if (skillsArray && skillsArray.length > 0) {
    skillsArray.forEach(skill => {
      const skillItem = $(`
        <span class="skill-tag">
          ${skill}
          <button type="button" class="skill-remove" data-skill="${skill}">×</button>
        </span>
      `);

      skillItem.find(".skill-remove").click(function () {
        removeProjectSkillFromEdit(itemId, skill, container);
      });

      skillsList.append(skillItem);
    });
  }

  skillCount.text(skillsArray ? skillsArray.length : 0);
}

function addProjectSkillToEdit(itemId, container) {
  const dropdown = container.find(`.project-skill-dropdown[data-id="${itemId}"]`);
  const selectedSkill = dropdown.val();

  if (!selectedSkill) return;

  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentSkills = [];
  
  try {
    const dataValue = skillsData.val();
    if (dataValue && dataValue.trim()) {
      currentSkills = JSON.parse(dataValue);
    }
  } catch (e) {
    console.warn("Error parsing skills data:", e);
    currentSkills = [];
  }

  if (currentSkills.includes(selectedSkill)) {
    showError("Duplicate Skill", "This skill is already added.");
    return;
  }

  currentSkills.push(selectedSkill);
  skillsData.val(JSON.stringify(currentSkills));

  // ✅ Update display
  const skillsList = container.find(`.project-skills-list[data-id="${itemId}"]`);
  const skillItem = $(`
    <span class="skill-tag">
      ${selectedSkill}
      <button type="button" class="skill-remove" data-skill="${selectedSkill}">×</button>
    </span>
  `);

  skillItem.find(".skill-remove").click(function () {
    removeProjectSkillFromEdit(itemId, selectedSkill, container);
  });

  skillsList.append(skillItem);

  // ✅ Update count
  const skillCount = container.find(`.project-skill-count[data-id="${itemId}"]`);
  skillCount.text(currentSkills.length);

  // ✅ Reset dropdown
  dropdown.val("");
  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).prop("disabled", true);
}


function removeProjectSkillFromEdit(itemId, skillToRemove, container) {
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentSkills = [];
  
  try {
    const dataValue = skillsData.val();
    if (dataValue && dataValue.trim()) {
      currentSkills = JSON.parse(dataValue);
    }
  } catch (e) {
    console.warn("Error parsing skills data:", e);
    currentSkills = [];
  }

  currentSkills = currentSkills.filter(skill => skill !== skillToRemove);
  skillsData.val(JSON.stringify(currentSkills));

  initializeProjectSkills(itemId, currentSkills);
}

function handleProjectImageChange(input, itemId) {
  try {
    const file = input.files && input.files[0];
    if (!file) return;

    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
      showError("File Too Large", "Image size must not exceed 10MB.");
      input.value = "";
      return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    if (!allowedTypes.includes(file.type)) {
      showError("Invalid File Type", "Only JPG, PNG, and GIF images are allowed.");
      input.value = "";
      return;
    }

    const allowedExtensions = ["jpg", "jpeg", "png", "gif"];
    const fileExtension = file.name.split(".").pop().toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
      showError("Invalid File Extension", "Unsupported file extension detected.");
      input.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      const imageUrl = e.target.result;

      const $previewContainer = $(`.project-image-preview[data-id="${itemId}"] img`);
      if ($previewContainer.length === 0) {
        console.warn(`Preview element for itemId=${itemId} not found.`);
        return;
      }

      if (!/^data:image\/(jpeg|png|gif);base64,/.test(imageUrl)) {
        showError("Invalid File Content", "The uploaded file is not a valid image.");
        input.value = "";
        return;
      }

      $previewContainer.attr("src", imageUrl);
      showToast("Image preview updated");
    };

    reader.readAsDataURL(file);
  } catch (err) {
    console.error("Image upload error:", err);
    showError("Unexpected Error", "Something went wrong while processing the image.");
    input.value = "";
  }
}

// ============================================
// 6️⃣ DOCUMENT READY (Event Handlers)
// ============================================
// ✅ ต้องอยู่ล่างสุด เพราะจะเรียกใช้ทุกฟังก์ชันที่อยู่ข้างบน

$(document).ready(function () {

  // =====================
  // 🔹 Initial Load
  // =====================
  const userID = $("#userID").val();
  if (userID) {
    loadWorkExp(userID);      // ✅ เรียกใช้ฟังก์ชันที่ประกาศข้างบน
    loadEducation(userID);    // ✅ เรียกใช้ฟังก์ชันที่ประกาศข้างบน
    loadProject(userID);     // ✅ เรียกใช้ฟังก์ชันที่ประกาศข้างบน
  }

  // =====================
  // 🔹 Toggle Buttons
  // =====================
  $(document).on("click", ".btn-toggle", function () {
    const target = $(this).data("target");
    $(target).toggleClass("hidden");
  });
  // =====================
  // 🔹 WORK EXPERIENCE EVENTS
  // =====================

  // Cancel Button
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
        $("#workEndDate").prop("disabled", false);
        showToast("Work Experience form has been cleared.");
      }
    });
  });

  // Checkbox Handler
  $("#workIsCurrent").change(function () {
    if ($(this).is(":checked")) {
      $("#workEndDate").val("").prop("disabled", true);
    } else {
      $("#workEndDate").prop("disabled", false);
    }
  });

  // Submit Handler
  $("#AddWorkExp").on("submit", function (e) {
    e.preventDefault();

    if (!validateWorkExpForm(this)) return; // ✅ เรียกใช้ validation

    const isCurrent = $("#workIsCurrent").is(":checked");
    const endDate = $("#workEndDate").val();

    if (!isCurrent && !endDate) {
      showError("Incomplete information", "Please select an End Date or check 'Currently working here'.");
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
          $("#workEndDate").prop("disabled", false);

          let userID = $("#userID").val();
          loadWorkExp(userID); // ✅ เรียกใช้ฟังก์ชัน load
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function () {
        showError("An error has occurred", "The Work Experience could not be saved.");
      },
    });
  });


  // =====================
  // 🔹 EDUCATION EVENTS
  // =====================

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

  // =====================
  // 🔹 PROJECT EVENTS
  // =====================

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


  $("#AddProject").on("submit", function (e) {
    e.preventDefault();

    if (!validateProjectForm(this)) return;

    const formData = new FormData(this);
    formData.append("userID", $("#userID").val());

    // ✅ ส่ง skills เป็น JSON array (ปลอดภัยสุด)
    const skillValues = $("#myProjectSkillsInput").val();
    formData.append("myProjectSkills", JSON.stringify(skillValues ? skillValues.split(",") : []));

    $.ajax({
      url: "/portfolio/project/insert-project.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === 1) {
          showToast("Project saved!");
          $("#AddProject")[0].reset();
          $("#AddProject").addClass("hidden");
          $("#projectSkillsList").empty();
          $("#projectSkillCount").text("0");
          $("#emptyProjectSkillsState").show();

          let userID = $("#userID").val();
          loadProject(userID);
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function () {
        showError("An error has occurred", "The Project could not be saved.");
      },
    });
  });


}); // ✅ ปิด $(document).ready()






