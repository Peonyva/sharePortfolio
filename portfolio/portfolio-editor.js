// 1️. Utility Functions (ฟังก์ชันช่วยเหลือ) //

// ต้องอยู่บนสุด เพราะฟังก์ชันอื่นจะเรียกใช้

async function showError(title, text) {
  return await Swal.fire({ icon: "error", title: title, text: text, });
}

//  Show Toast (Alert Top Right)
async function showSuccess(title) {
  return await Swal.fire({ icon: "success", title: title, });
}

async function showWarning(title) {
  const result = await Swal.fire({
    icon: "warning",
    title: title,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes"
  });

  return result.isConfirmed;
}

// 2️. Valication Functions (ฟังก์ชันตรวจสอบข้อมูล) //

// อยู่ก่อน CRUD Functions เพราะจะถูกเรียกใช้ใน Submit

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

// 3. Profile - CRUD Functions //

function loadUserProfile(userID) {
  $.ajax({
    url: "/portfolio/profile/get-profile.php",
    method: "GET",
    data: { userID: userID },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        const data = response.data;
        const selectedSkills = response.selectedSkills || [];

        $("#firstname").val(data.firstname || '');
        $("#lastname").val(data.lastname || '');
        $("#birthdate").val(data.birthdate || '');
        $("#email").val(data.email || '');
        $("#phone").val(data.phone || '');
        $("#ProfessionalTitle").val(data.professionalTitle || '');
        $("#facebook").val(data.facebook || '');
        $("#facebookUrl").val(data.facebookUrl || '');

        // แสดงข้อมูล About Me และ Skills Description
        $("#introContent").val(data.introContent || '');
        $("#skillsContent").val(data.skillsContent || '');

        loadUserSkills(selectedSkills);

        // แสดงรูปภาพ
        if (data.logoImage) {
          showExistingImagePreview('logoImageUploader', 'logoImage', data.logoImage);
        }
        if (data.profileImage) {
          showExistingImagePreview('profileImageUploader', 'profileImage', data.profileImage);
        }
        if (data.coverImage) {
          showExistingImagePreview('coverImageUploader', 'coverImage', data.coverImage);
        }

      } else {
        showError("Failed to load profile", response.message || "Please try again.");
      }
    },
    error: function (xhr, status, error) {
      let msg = "Cannot load profile data.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showError("Error", msg);
    }
  });
}


function loadUserSkills(selectedSkills) {
  // ตรวจสอบว่า allSkills มีค่าหรือไม่
  if (!allSkills || allSkills.length === 0) {
    showError("Error", "Skills data not available. Please refresh the page.");
    return;
  }

  // Clear และเพิ่ม skills
  mySkills = [];
  if (selectedSkills && selectedSkills.length > 0) {
    selectedSkills.forEach(function (skill) {
      const skillsId = parseInt(skill.skillsID || skill.id);
      if (!isNaN(skillsId)) {
        mySkills.push(skillsId);
      }
    });

    updateSkillsDisplay();
    populateSkillsDropdown();
    updateMySkillsInput();

  } else {
    const emptyState = document.getElementById("emptySkillsState");
    const mySkillsBox = document.getElementById("mySkillsBox");

    if (emptyState) emptyState.style.display = "block";
    if (mySkillsBox) mySkillsBox.style.display = "none";
    updateMySkillsInput();
  }
}

function showExistingImagePreview(divPreviewID, inputFileID, imagePath) {
  const uploader = document.getElementById(divPreviewID);
  if (!uploader) {
    showError("Upload Image Failed", `Upload container '${divPreviewID}' not found`);
    return;
  }
  uploader.innerHTML = createImagePreviewHTML(imagePath, inputFileID, divPreviewID);
}



// 4. Work Experience - CRUD Functions //

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

// LOAD (ดึงข้อมูลจาก Server)
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

  let workItem =
    $(`
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
                <label>Remark :</label>
                <textarea class="work-remark" data-id="${itemId}" name="remark">${data.remark || ""}</textarea>
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
}

// UPDATE
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
  const remark = container.find(".work-remark").val();

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
      remark: remark,
    },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        showSuccess("Work Experience updated successfully!");
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function (xhr, status, error) {
      let errorMessage = "Could not update item.";
      // ดึงข้อความที่ PHP ส่งมา (เช่น "End date must be after start date")
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      showError("Update Failed", errorMessage);
    },


  });
}

// DELETE
function deleteWorkItem(itemId, container) {
  const userID = $("#userID").val();

  Swal.fire({
    title: "Confirm deletion?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/portfolio/workExperience/delete-work.php",
        method: "POST",
        data: {
          id: itemId,
          userID: userID,
        },
        dataType: "json",
        success: function (response) {
          if (response.status === 1) {
            showSuccess("Work Experience deleted successfully!");

            // Reload ข้อมูลใหม่ของ user เดียวกัน
            loadWorkExp(userID);
          } else {
            showError("Deletion failed", response.message || "Please try again.");
          }
        },
        error: function (xhr, status, error) {
          let errorMessage = "Could not delete item.";
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          showError("Deletion Failed", errorMessage);
        },
      });
    }
  });
}

// MOVE (Up/Down)
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
        loadWorkExp($("#userID").val());
      } else {
        console.error("Error: " + response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      // เพิ่มส่วนนี้เข้าไปให้เหมือน function move อื่นๆ
      let msg = "Failed to move item.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showError("Error", msg);
    },
  });
}

// 5. Education - CRUD Functions

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
      let msg = "Failed to load education data.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showError("Error", msg);
    },
  });
}

function appendEducationItem(data, allData) {
  let sortOrder = parseInt(data.sortOrder);
  let itemId = data.id;
  let totalItems = allData.length;

  let container = $(`<div class="education-item-container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`);

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
        <label>Remark :</label>
        <textarea class="edu-remark" data-id="${itemId}">${data.remark || ""}</textarea>
      </div>

      <div class="btn-wrapper">
        <button type="button" class="btn btn-success btn-update-edu btn-manage" data-id="${itemId}">Update</button>
        <button type="button" class="btn btn-danger btn-delete-edu btn-manage" data-id="${itemId}">Delete</button>
      </div>
    </div>
  `);

  $("#Education").append(container);
  container.append(educationItem);

  // Event Handlers

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
  const remark = container.find(".edu-remark").val().trim();

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
      remark: remark,
    },
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        showSuccess("Education updated successfully!");
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function (xhr, status, error) {
      let errorMessage = "Could not update item.";
      // ดึงข้อความที่ PHP ส่งมา (เช่น "End date must be after start date")
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      showError("Update Failed", errorMessage);
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
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/portfolio/education/delete-education.php",
        method: "POST",
        data: {
          id: itemId,
          userID: userID, // ส่งค่า userID ไปด้วย
        },
        dataType: "json",
        success: function (response) {
          if (response.status === 1) {
            showSuccess("Education deleted successfully!");

            //  Reload ข้อมูลใหม่ของ user เดียวกัน
            loadEducation(userID);
          } else {
            showError("Deletion failed", response.message || "Please try again.");
          }
        },
        error: function (xhr, status, error) {
          let errorMessage = "Could not delete item.";
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          showError("Deletion Failed", errorMessage);
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
      let msg = "Failed to move item.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showError("Error", msg);
    },
  });
}


// 6. Project - CRUD Functions //

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
        // เรียงลำดับ
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

function appendProjectItem(data, allData) {
  let sortOrder = parseInt(data.sortOrder);
  let itemId = String(data.projectID).trim();
  let totalItems = allData.length;

  let container = $(`<div class="project-item-container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`);

  // --- Buttons Logic ---
  let upButton = sortOrder > 1 ?
    `<button type="button" class="btn btn-secondary move-up-project-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
       <i class="fa-solid fa-arrow-up"></i> Up
     </button>` : "";

  let downButton = sortOrder < totalItems ?
    `<button type="button" class="btn btn-secondary move-down-project-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
       <i class="fa-solid fa-arrow-down"></i> Down
     </button>` : "";

  // --- Skills Parsing Logic ---
  let skillsArray = []; // ชื่อ Skill (Display)
  let skillsIdsArray = []; // ID Skill (Hidden)

  // รองรับทั้งแบบ Array of Objects (จาก JSON) และ String
  if (Array.isArray(data.skills)) {
    skillsArray = data.skills.map(s => s.skillsName || s.name);
    skillsIdsArray = data.skills.map(s => s.skillsID || s.id);
  } else if (typeof data.skills === "string" && data.skills.trim()) {
    let skillsStr = data.skills.trim();
    if (skillsStr.startsWith("[") && skillsStr.endsWith("]")) {
      try {
        let parsed = JSON.parse(skillsStr);
        skillsArray = parsed.map(s => s.skillsName || s.name || s);
        skillsIdsArray = parsed.map(s => s.skillsID || s.id);
      } catch (e) {
        // Fallback
        skillsArray = skillsStr.replace(/[\[\]"]/g, "").split(",").map(s => s.trim()).filter(s => s);
      }
    }
  }

  // สร้าง HTML สำหรับแสดงผล Skills (โหมดดูปกติ)
  const skillsDisplayHTML = skillsArray.length
    ? skillsArray.map(skill => `<span class="skill-tag">${skill}</span>`).join("")
    : `<span class="text-muted">No skills selected</span>`;

  // --- Build HTML ---
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
        <input type="text" class="project-title form-control" data-id="${itemId}" value="${data.projectTitle}">
      </div>

      <div class="form-group">
        <label class="required-label">Project Image :</label>
        <div class="project-image-preview image-preview" data-id="${itemId}">
          <img src="${data.projectImage}" alt="${data.projectTitle}" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image';">
        </div>
        
        <div class="preview-actions mt-2">
            <button type="button" class="btn btn-primary btn-change-image btn-preview-image" data-id="${itemId}">Change Image</button>
            <input type="file" class="project-image-input hidden" data-id="${itemId}" accept="image/*" style="display:none;">
        </div>
      </div>

      <div class="form-group">
          <label class="required-label">Key Point :</label>
          <textarea class="project-keypoint form-control" data-id="${itemId}" rows="3">${data.keyPoint}</textarea>
          <div class="description-message text-muted small">Press Enter to separate each item onto a new line.</div>
      </div>

      <div class="form-group">
        <label class="required-label">Skills :</label>
        <div class="skills-list project-skills-display" data-id="${itemId}">
          ${skillsDisplayHTML}
        </div>
      </div>

      <div class="project-skills-editor hidden" data-id="${itemId}" style="display:none;">
          <div class="skill-editor-container card p-3 bg-light">
              <div class="input-group mb-3">
                  <select class="project-skill-dropdown form-select" data-id="${itemId}">
                      <option value="">Loading skills...</option>
                  </select>
                  <button type="button" class="btn-add-project-skill btn btn-success" data-id="${itemId}" disabled>
                      Add Skill
                  </button>
              </div>

            <div class="selected-skills-box" data-id="${itemId}">
                <h5>Selected Skills (<span class="project-skill-count" data-id="${itemId}">0</span>)</h5>
                <div class="project-skills-list" data-id="${itemId}"></div>
            </div>

            <input type="hidden" class="project-skills-data" data-id="${itemId}" value='${JSON.stringify(skillsIdsArray)}'>
        </div>
      </div>

      <div class="btn-wrapper mt-3 d-flex gap-2">
        <button type="button" class="btn-edit-skills btn btn-primary btn-manage" data-id="${itemId}">Edit Skills</button>
        <button type="button" class="btn-update-project btn btn-success btn-manage" data-id="${itemId}">Update</button>
        <button type="button" class="btn-delete-project btn btn-danger btn-manage" data-id="${itemId}">Delete</button>
      </div>
    </div>
  `);

  $("#Project").append(container);
  container.append(projectItem);

  // --- Initialize Logic for this Item ---
  // 1. Load Skill Names/IDs ลงใน Editor List
  initializeProjectSkills(itemId, skillsArray, skillsIdsArray);

  // Move Up/Down
  container.find(".move-up-project-btn").click(function () {
    moveProjectItem(itemId, parseInt($(this).data("current-sort")), parseInt($(this).data("current-sort")) - 1);
  });
  container.find(".move-down-project-btn").click(function () {
    moveProjectItem(itemId, parseInt($(this).data("current-sort")), parseInt($(this).data("current-sort")) + 1);
  });

  // Image Handling
  container.find(".btn-change-image").click(function () {
    container.find(`.project-image-input[data-id="${itemId}"]`).click();
  });
  container.find(`.project-image-input[data-id="${itemId}"]`).change(function () {
    handleProjectImageChange(this, itemId);
  });

  // Toggle Skills Editor
  container.find(".btn-edit-skills").click(function () {
    const editor = container.find(`.project-skills-editor[data-id="${itemId}"]`);
    const display = container.find(`.project-skills-display[data-id="${itemId}"]`);

    if (editor.is(":visible")) {
      // ปิด Editor -> อัปเดต Display
      editor.slideUp();
      display.slideDown();
      $(this).text("Edit Skills");
      // Update display from hidden input logic (optional refinement)
    } else {
      // เปิด Editor -> โหลด Dropdown
      editor.removeClass("hidden").slideDown();
      display.slideUp();
      $(this).text("Done Editing");
      loadSkillsForProjectEdit(itemId); // โหลด options ใส่ dropdown
    }
  });

  // Dropdown Change
  container.find(`.project-skill-dropdown[data-id="${itemId}"]`).change(function () {
    const btn = container.find(`.btn-add-project-skill[data-id="${itemId}"]`);
    btn.prop("disabled", !$(this).val());
  });

  // Add Skill Button
  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).click(function () {
    addProjectSkillToEdit(itemId, container);
  });

  // Update & Delete Buttons
  container.find(".btn-update-project").click(function () {
    updateProjectItem(itemId, container);
  });
  container.find(".btn-delete-project").click(function () {
    deleteProjectItem(itemId, container);
  });
}


function updateProjectItem(itemId, container) {
  const title = container.find(".project-title").val().trim();
  const keyPoint = container.find(".project-keypoint").val().trim();

  // ดึง Skill IDs (ไม่ใช่ชื่อ)
  const skillsDataStr = container.find(`.project-skills-data[data-id="${itemId}"]`).val();
  let skillsIds = [];

  try {
    skillsIds = JSON.parse(skillsDataStr) || [];
  } catch (e) {
    console.warn("Error parsing skills:", e);
    skillsIds = [];
  }

  if (!title) {
    showError("Validation Error", "Project title is required.");
    return;
  }

  if (!keyPoint) {
    showError("Validation Error", "Job description is required.");
    return;
  }

  if (skillsIds.length === 0) {
    showError("Validation Error", "Please select at least one skill.");
    return;
  }

  const formData = new FormData();
  formData.append("id", String(itemId).trim());
  formData.append("userID", $("#userID").val());
  formData.append("projectTitle", title);
  formData.append("keyPoint", keyPoint);

  // ส่ง Skill IDs โดยตรง
  formData.append("myProjectSkills", JSON.stringify(skillsIds));

  const imageInput = container.find(`.project-image-input[data-id="${itemId}"]`)[0];
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
      if (response.status === 1) {
        showSuccess("Project updated successfully!");
        loadProject($("#userID").val());
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function (xhr, status, error) {
      let errorMessage = "Could not update item.";
      // ดึงข้อความที่ PHP ส่งมา
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      showError("Update Failed", errorMessage);
    },
  });
}


function deleteProjectItem(itemId, container) {
  const userID = $("#userID").val();

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
    text: "This action cannot be undone",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes"
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
          if (response.status === 1) {
            showSuccess("Project deleted successfully!");
            loadProject(safeUserID);
          } else {
            showError("Deletion failed", response.message || "Please try again.");
          }
        },
        error: function (xhr, status, error) {
          let errorMessage = "Could not delete item.";
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          showError("Deletion Failed", errorMessage);
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
      let msg = "Failed to move item.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showError("Error", msg);
    },
  });
}


// Project - Helper Functions

function loadSkillsForProjectEdit(itemId) {
  $.ajax({
    url: "/portfolio/get-skills.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      let skillsData = [];

      if (response.status === 1 && Array.isArray(response.data)) {
        skillsData = response.data;
      } else if (Array.isArray(response)) {
        skillsData = response;
      }

      const dropdown = $(`.project-skill-dropdown[data-id="${itemId}"]`);
      dropdown.empty().append('<option value="">Choose a skill...</option>');

      skillsData.forEach(function (skill) {
        const skillName = skill.name || skill.skillName;
        const skillsId = skill.skillsId || skill.id;

        dropdown.append(`<option value="${skillName}" data-skill-id="${skillsId}">${skillName}</option>`);
      });
    },
    error: function () {
      console.error("Failed to load skills for project edit");
    },
  });
}

function initializeProjectSkills(itemId, skillNames, skillsIds) {
  const container = $(`.project-item-container[data-id="${itemId}"]`);
  const skillsList = container.find(`.project-skills-list[data-id="${itemId}"]`);
  const skillCount = container.find(`.project-skill-count[data-id="${itemId}"]`);

  if (!skillsList.length) {
    console.warn(`Skills list not found for itemId=${itemId}`);
    return;
  }

  skillsList.empty();

  if (skillNames && skillNames.length > 0) {
    skillNames.forEach((skill, index) => {
      const skillsId = skillsIds[index] || '';  //  ได้ ID ของ skill
      const skillItem = $(`
                <span class="skill-tag" data-skill-id="${skillsId}">${skill}
                  <button type="button" class="skill-remove" data-skill="${skill}" data-skill-id="${skillsId}">×</button>
                </span>
            `);

      skillItem.find(".skill-remove").click(function () {
        removeProjectSkillFromEdit(itemId, skill, skillsId, container);
      });

      skillsList.append(skillItem);
    });
  }

  skillCount.text(skillNames ? skillNames.length : 0);
}

function addProjectSkillToEdit(itemId, container) {
  const dropdown = container.find(`.project-skill-dropdown[data-id="${itemId}"]`);
  const selectedSkill = dropdown.val();
  const selectedskillsId = dropdown.find("option:selected").data("skill-id");  // ดึง ID

  if (!selectedSkill || !selectedskillsId) return;

  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentskillsIds = [];

  try {
    const dataValue = skillsData.val();
    if (dataValue && dataValue.trim()) {
      currentskillsIds = JSON.parse(dataValue);
    }
  } catch (e) {
    console.warn("Error parsing skills data:", e);
    currentskillsIds = [];
  }

  // ตรวจสอบ Skill ID ที่ซ้ำ (ไม่ใช่ชื่อ)
  if (currentskillsIds.includes(parseInt(selectedskillsId))) {
    showError("Duplicate Skill", "This skill is already added.");
    return;
  }

  currentskillsIds.push(parseInt(selectedskillsId));
  skillsData.val(JSON.stringify(currentskillsIds));

  // แสดงผล
  const skillsList = container.find(`.project-skills-list[data-id="${itemId}"]`);
  const skillItem = $(`
        <span class="skill-tag" data-skill-id="${selectedskillsId}">${selectedSkill}
          <button type="button" class="skill-remove" data-skill="${selectedSkill}" data-skill-id="${selectedskillsId}">×</button>
        </span>
    `);

  skillItem.find(".skill-remove").click(function () {
    removeProjectSkillFromEdit(itemId, selectedSkill, selectedskillsId, container);
  });

  skillsList.append(skillItem);

  const skillCount = container.find(`.project-skill-count[data-id="${itemId}"]`);
  skillCount.text(currentskillsIds.length);

  dropdown.val("");
  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).prop("disabled", true);
}


function removeProjectSkillFromEdit(itemId, skillName, skillsId, container) {
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentskillsIds = [];

  try {
    const dataValue = skillsData.val();
    if (dataValue && dataValue.trim()) {
      currentskillsIds = JSON.parse(dataValue);
    }
  } catch (e) {
    console.warn("Error parsing skills data:", e);
    currentskillsIds = [];
  }

  // ลบ Skill ID (ไม่ใช่ชื่อ)
  currentskillsIds = currentskillsIds.filter(id => id !== parseInt(skillsId));
  skillsData.val(JSON.stringify(currentskillsIds));

  //  ดึง skill names มาแสดง (จาก dropdown หรือ display)
  const dropdownOptions = container.find(`.project-skill-dropdown[data-id="${itemId}"] option`);
  let skillNames = [];
  dropdownOptions.each(function () {
    const val = $(this).val();
    if (val && currentskillsIds.includes(parseInt($(this).data("skill-id")))) {
      skillNames.push(val);
    }
  });

  initializeProjectSkills(itemId, skillNames, currentskillsIds);
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
      showSuccess("Image preview updated");
    };

    reader.readAsDataURL(file);
  } catch (err) {
    console.error("Image upload error:", err);
    showError("Unexpected Error", "Something went wrong while processing the image.");
    input.value = "";
  }
}

// 7. DOCUMENT READY (Event Handlers) //
// ต้องอยู่ล่างสุด เพราะจะเรียกใช้ทุกฟังก์ชันที่อยู่ข้างบน

$(document).ready(async function () {
  const userID = $("#userID").val();

  if (userID) {
    try {
      //  รอให้ Skills โหลดเสร็จก่อน
      await initializeApp();

      // แล้วค่อยโหลดข้อมูลทั้งหมด
      loadUserProfile(userID);
      loadWorkExp(userID);
      loadEducation(userID);
      loadProject(userID);

    } catch (error) {
      showError("Initialization Error", "Failed to load application data. Please refresh the page.");
    }
  }

  // toggle form buttons (เปิด/ปิดฟอร์ม)

  $(".btn-toggle").click(function () {
    const targetId = $(this).data("target");
    const $targetForm = $(targetId);

    if ($targetForm.length) {
      const $icon = $(this).find("i");

      if ($targetForm.hasClass("hidden")) {
        // เปิดฟอร์ม
        $targetForm.removeClass("hidden").hide().slideDown(300);
        $icon.removeClass("fa-plus").addClass("fa-minus");

      } else {
        // ปิดฟอร์ม
        $targetForm.slideUp(300, function () {
          $(this).addClass("hidden");
        });
        $icon.removeClass("fa-minus").addClass("fa-plus");
      }
    } else {
      console.warn(`Target form not found: ${targetId}`);
    }
  });


  // save profile to db

  $("#personalForm").on("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    formData.append("userID", $("#userID").val());

    const mySkills = $("#mySkillsInput").val()?.trim() || "";
    formData.append("mySkills", mySkills);

    $.ajax({
      url: "/portfolio/profile/save-profile.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {

        if (response.status) {
          showSuccess("Personal saved!");
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "The Work Experience could not be saved.";
        // นี่คือจุดสำคัญ! PHP จะส่ง Validation Error มาทางนี้
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showError("Save Failed", errorMessage);
      },
    });
  });



  //  Work Experience Events

  // Cancel Button
  $("#btnCancelWorkExp").click(function () {

    Swal.fire({
      title: "Confirm cancellation",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes"
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddWorkExp")[0].reset();
        $("#workEndDate").prop("disabled", false);

        // ปิดฟอร์มหลัง Cancel
        $("#AddWorkExp").slideUp(300, function () {
          $(this).addClass("hidden");
        });

        // เปลี่ยนไอคอนกลับเป็น +
        $(".btn-toggle[data-target='#AddWorkExp'] i")
          .removeClass("fa-minus")
          .addClass("fa-plus");

        showSuccess("Work Experience form has been cleared.");
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

    if (!validateWorkExpForm(this)) return; // เรียกใช้ validation

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
          showSuccess("Work Experience saved!");
          $("#AddWorkExp").addClass("hidden");
          $("#AddWorkExp")[0].reset();
          $("#workEndDate").prop("disabled", false);

          // ปิดฟอร์มหลัง Save
          $("#AddWorkExp").slideUp(300, function () {
            $(this).addClass("hidden");
          });

          // เปลี่ยนไอคอนกลับเป็น +
          $(".btn-toggle[data-target='#AddWorkExp'] i")
            .removeClass("fa-minus")
            .addClass("fa-plus");

          let userID = $("#userID").val();
          loadWorkExp(userID); // เรียกใช้ฟังก์ชัน load
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "The Work Experience could not be saved.";
        // นี่คือจุดสำคัญ! PHP จะส่ง Validation Error มาทางนี้
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showError("Save Failed", errorMessage);
      },
    });
  });


  // Education Events

  $("#btnCancelEducation").click(function () {

    Swal.fire({
      title: "Confirm cancellation?",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes"
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddEducation")[0].reset();

        // ปิดฟอร์มหลัง Cancel
        $("#AddEducation").slideUp(300, function () {
          $(this).addClass("hidden");
        });

        // เปลี่ยนไอคอนกลับเป็น +
        $(".btn-toggle[data-target='#AddEducation'] i")
          .removeClass("fa-minus")
          .addClass("fa-plus");

        showSuccess("Education form has been cleared.");
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
          showSuccess("Education saved!");
          $("#AddEducation").addClass("hidden");
          $("#AddEducation")[0].reset();

          // ปิดฟอร์มหลัง Save
          $("#AddEducation").slideUp(300, function () {
            $(this).addClass("hidden");
          });

          // เปลี่ยนไอคอนกลับเป็น +
          $(".btn-toggle[data-target='#AddEducation'] i")
            .removeClass("fa-minus")
            .addClass("fa-plus");

          let userID = $("#userID").val();
          loadEducation(userID);
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "The Education could not be saved.";
        // นี่คือจุดสำคัญ! PHP จะส่ง Validation Error มาทางนี้
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showError("Save Failed", errorMessage);
      },
    });
  });

  // Project Events

  $("#btnCancelProject").click(function () {
    Swal.fire({
      title: "Confirm cancellation?",
      text: "All entered information will be cleared.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes"
    }).then((result) => {
      if (result.isConfirmed) {
        $("#AddProject")[0].reset();

        // ปิดฟอร์มหลัง Cancel
        $("#AddProject").slideUp(300, function () {
          $(this).addClass("hidden");
        });

        // เปลี่ยนไอคอนกลับเป็น +
        $(".btn-toggle[data-target='#AddProject'] i")
          .removeClass("fa-minus")
          .addClass("fa-plus");

        showSuccess("Project form has been cleared.");
      }
    });
  });

  $("#AddProject").on("submit", function (e) {
    e.preventDefault();

    if (!validateProjectForm(this)) return;

    const formData = new FormData(this);
    formData.append("userID", $("#userID").val());

    // ส่ง skills เป็น JSON array (ปลอดภัยสุด)
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
          showSuccess("Project saved!");
          $("#AddProject")[0].reset();
          $("#AddProject").addClass("hidden");
          $("#projectSkillsList").empty();
          $("#projectSkillCount").text("0");
          $("#emptyProjectSkillsState").show();

          // ปิดฟอร์มหลัง Save
          $("#AddProject").slideUp(300, function () {
            $(this).addClass("hidden");
          });

          // เปลี่ยนไอคอนกลับเป็น +
          $(".btn-toggle[data-target='#AddProject'] i")
            .removeClass("fa-minus")
            .addClass("fa-plus");

          let userID = $("#userID").val();
          loadProject(userID);
        } else {
          showError("An error occurred", response.message || "Please try again.");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "The Project could not be saved.";
        // นี่คือจุดสำคัญ! PHP จะส่ง Validation Error มาทางนี้
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showError("Save Failed", errorMessage);
      },
    });
  });


}); // ปิด $(document).ready()






