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
function appendProjectItem(data, allData) {
  let sortOrder = parseInt(data.sortOrder);
  let itemId = data.id; // ✅ itemId ประกาศไว้ที่นี่
  let totalItems = allData.length;

  console.log("📦 Appending Project Item:", { itemId, sortOrder }); // Debug

  let container = $(
    `<div class="project-item-container" data-id="${itemId}" data-sort-order="${sortOrder}"></div>`
  );

  // ปุ่ม Up/Down
  let upButton = "";
  let downButton = "";

  if (totalItems > 1) {
    if (sortOrder > 1) {
      upButton = `<button type="button" class="btn btn-secondary move-up-project-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
        <i class="fa-solid fa-arrow-up"></i> Up
      </button>`;
    }
    if (sortOrder < totalItems) {
      downButton = `<button type="button" class="btn btn-secondary move-down-project-btn btn-manage" data-id="${itemId}" data-current-sort="${sortOrder}">
        <i class="fa-solid fa-arrow-down"></i> Down
      </button>`;
    }
  }

  // แปลง skills จาก JSON string เป็น array
  let skillsArray = [];
  try {
    skillsArray = JSON.parse(data.skills || "[]");
  } catch (e) {
    console.error("Error parsing skills:", e);
    skillsArray = [];
  }

  // สร้าง HTML สำหรับ skills
  let skillsHTML = '';
  if (skillsArray.length > 0) {
    skillsHTML = skillsArray.map(skill =>
      `<span class="skill-badge">${skill}</span>`
    ).join('');
  } else {
    skillsHTML = '<span class="htmlforSkills">No skills selected</span>';
  }

  let projectItem = $(`
    <div class="project-item">
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
        <input type="text" class="project-title" data-id="${itemId}" value="${data.projectTitle}">
      </div>

      <div class="form-group">
        <label class="required-label">Project Image :</label>
        <div class="project-image-preview image-preview" data-id="${itemId}">
          <img src="${data.projectImage}" alt="${data.projectTitle}">
        </div>
        <div class="mt-2">
          <button type="button" class="btn btn-secondary btn-change-image btn-preview-image" data-id="${itemId}">
            <i class="fa-solid fa-image"></i> Change Image
          </button>
          <input type="file" class="project-image-input hidden" data-id="${itemId}" accept="image/*">
        </div>
      </div>

      <div class="form-group">
        <label class="required-label">Job Description :</label>
        <textarea class="project-keypoint" data-id="${itemId}" rows="3">${data.keyPoint}</textarea>
        <div class="description-message">Press Enter to separate each item onto a new line.</div>
      </div>

      <div class="form-group">
        <label class="required-label">Skills :</label>
        <div class="project-skills-display" data-id="${itemId}">
          ${skillsHTML}
        </div>
        <button type="button" class="btn btn-secondary btn-edit-skills mt-2" data-id="${itemId}">
          <i class="fa-solid fa-edit"></i> Edit Skills
        </button>
      </div>

      <!-- Hidden Skills Editor -->
      <div class="project-skills-editor hidden" data-id="${itemId}">
        <div class="input-group">
          <div class="form-group dropdown">
            <label>Select Skill :</label>
            <select class="form-select project-skill-dropdown" data-id="${itemId}">
              <option value="">Choose a skill...</option>
            </select>
          </div>
          <div class="btn-wrapper">
            <button type="button" class="btn btn-success btn-add-project-skill" data-id="${itemId}" disabled>
              Add Skill
            </button>
          </div>
        </div>
        <div class="selected-skills-box">
          <h5>Selected Skills (<span class="project-skill-count" data-id="${itemId}">0</span>)</h5>
          <div class="project-skills-list" data-id="${itemId}"></div>
        </div>
        <input type="hidden" class="project-skills-data" data-id="${itemId}" value='${data.skills}'>
      </div>

      <div class="btn-wrapper">
        <button type="button" class="btn btn-success btn-update-project btn-manage" data-id="${itemId}">Update</button>
        <button type="button" class="btn btn-danger btn-delete-project btn-manage" data-id="${itemId}">Delete</button>
      </div>
    </div>
  `);

  $("#Project").append(container);
  container.append(projectItem);

  // ✅ Load skills dropdown สำหรับ edit mode
  loadSkillsForProjectEdit(itemId);

  // ✅ Initialize selected skills
  initializeProjectSkills(itemId, skillsArray);

  // =============================
  // Event Handlers
  // =============================

  // ปุ่ม Move Up
  container.find(".move-up-project-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveProjectItem(itemId, currentSort, currentSort - 1);
  });

  // ปุ่ม Move Down
  container.find(".move-down-project-btn").click(function () {
    let currentSort = parseInt($(this).data("current-sort"));
    moveProjectItem(itemId, currentSort, currentSort + 1);
  });

  // ปุ่ม Change Image
  container.find(".btn-change-image").click(function () {
    container.find(`.project-image-input[data-id="${itemId}"]`).click();
  });

  // เมื่อเลือกรูปใหม่
  container.find(`.project-image-input[data-id="${itemId}"]`).change(function () {
    handleProjectImageChange(this, itemId);
  });

  // ปุ่ม Edit Skills
  container.find(".btn-edit-skills").click(function () {
    container.find(`.project-skills-editor[data-id="${itemId}"]`).toggleClass("hidden");
  });

  // Dropdown skills change
  container.find(`.project-skill-dropdown[data-id="${itemId}"]`).change(function () {
    const btn = container.find(`.btn-add-project-skill[data-id="${itemId}"]`);
    if ($(this).val()) {
      btn.prop("disabled", false);
    } else {
      btn.prop("disabled", true);
    }
  });

  // ปุ่ม Add Skill
  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).click(function () {
    addProjectSkillToEdit(itemId, container);
  });

  // ปุ่ม Update
  container.find(".btn-update-project").click(function () {
    console.log("🔄 Update clicked for itemId:", itemId); // Debug
    updateProjectItem(itemId, container);
  });

  // ปุ่ม Delete
  container.find(".btn-delete-project").click(function () {
    console.log("🗑️ Delete clicked for itemId:", itemId); // Debug
    deleteProjectItem(itemId, container);
  });
}

// =============================
// 🔹 PROJECT - HELPER FUNCTIONS
// =============================

function loadSkillsForProjectEdit(itemId) {
  $.ajax({
    url: "/portfolio/personal/get-skills.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        const dropdown = $(`.project-skill-dropdown[data-id="${itemId}"]`);
        dropdown.empty().append('<option value="">Choose a skill...</option>');

        response.data.forEach(function (skill) {
          dropdown.append(`<option value="${skill.skillName}">${skill.skillName}</option>`);
        });
      }
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

  skillsList.empty();

  if (skillsArray.length > 0) {
    skillsArray.forEach(skill => {
      const skillItem = $(`
        <div class="skill-item">
          <span class="skill-name">${skill}</span>
          <button type="button" class="btn-remove-skill" data-skill="${skill}">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
      `);

      skillItem.find(".btn-remove-skill").click(function () {
        removeProjectSkillFromEdit(itemId, skill, container);
      });

      skillsList.append(skillItem);
    });
  }

  skillCount.text(skillsArray.length);
}

function addProjectSkillToEdit(itemId, container) {
  const dropdown = container.find(`.project-skill-dropdown[data-id="${itemId}"]`);
  const selectedSkill = dropdown.val();

  if (!selectedSkill) return;

  // ดึง skills ปัจจุบัน
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentSkills = [];
  try {
    currentSkills = JSON.parse(skillsData.val() || "[]");
  } catch (e) {
    currentSkills = [];
  }

  // เช็คว่ามีอยู่แล้วหรือไม่
  if (currentSkills.includes(selectedSkill)) {
    showError("Duplicate Skill", "This skill is already added.");
    return;
  }

  // เพิ่ม skill ใหม่
  currentSkills.push(selectedSkill);
  skillsData.val(JSON.stringify(currentSkills));

  // Refresh display
  initializeProjectSkills(itemId, currentSkills);

  // Reset dropdown
  dropdown.val("");
  container.find(`.btn-add-project-skill[data-id="${itemId}"]`).prop("disabled", true);
}

function removeProjectSkillFromEdit(itemId, skillToRemove, container) {
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`);
  let currentSkills = [];
  try {
    currentSkills = JSON.parse(skillsData.val() || "[]");
  } catch (e) {
    currentSkills = [];
  }

  // ลบ skill
  currentSkills = currentSkills.filter(skill => skill !== skillToRemove);
  skillsData.val(JSON.stringify(currentSkills));

  // Refresh display
  initializeProjectSkills(itemId, currentSkills);
}

function handleProjectImageChange(input, itemId) {
  const file = input.files[0];
  if (!file) return;

  // Validation
  if (file.size > 10485760) {
    showError("File Too Large", "Image size must not exceed 10MB.");
    input.value = "";
    return;
  }

  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  if (!allowedTypes.includes(file.type)) {
    showError("Invalid File Type", "Only JPG, PNG, and GIF images are allowed.");
    input.value = "";
    return;
  }

  // แสดง preview
  const reader = new FileReader();
  reader.onload = function (e) {
    $(`.project-image-preview[data-id="${itemId}"] img`).attr("src", e.target.result);
  };
  reader.readAsDataURL(file);
}

function updateProjectItem(itemId, container) {
  const title = container.find(".project-title").val().trim();
  const keyPoint = container.find(".project-keypoint").val().trim();
  const skillsData = container.find(`.project-skills-data[data-id="${itemId}"]`).val();
  const imageInput = container.find(`.project-image-input[data-id="${itemId}"]`)[0];

  // Validation
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

  // สร้าง FormData
  const formData = new FormData();
  formData.append("id", itemId);
  formData.append("userID", $("#userID").val());
  formData.append("projectTitle", title);
  formData.append("keyPoint", keyPoint);
  formData.append("myProjectSkills", skillsData);

  // ถ้ามีการเปลี่ยนรูป
  if (imageInput.files.length > 0) {
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
        showToast("Project updated successfully!");
        loadProject($("#userID").val());
      } else {
        showError("Update failed", response.message || "Please try again.");
      }
    },
    error: function () {
      showError("An error occurred", "Could not update Project.");
    },
  });
}

function deleteProjectItem(itemId, container) {
  const userID = $("#userID").val();

  // ✅ Debug: ตรวจสอบค่าทั้งหมด
  console.log("🔍 deleteProjectItem called with:", {
    itemId: itemId,
    itemIdType: typeof itemId,
    userID: userID,
    userIDType: typeof userID,
    containerExists: container.length > 0
  });

  // ⚠️ Validation: ตรวจสอบค่าก่อนส่ง
  if (!itemId || itemId === "undefined") {
    showError("Error", "Project ID is missing or invalid.");
    console.error("❌ itemId is invalid:", itemId);
    return;
  }

  if (!userID || userID === "undefined") {
    showError("Error", "User ID is missing or invalid.");
    console.error("❌ userID is invalid:", userID);
    return;
  }

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
      // ✅ สร้าง FormData เพื่อให้แน่ใจว่าส่งข้อมูลถูกต้อง
      const formData = new FormData();
      formData.append("id", itemId);
      formData.append("userID", userID);

      // Debug: ดูข้อมูลที่จะส่ง
      console.log("📤 Sending data:", {
        id: itemId,
        userID: userID
      });

      $.ajax({
        url: "/portfolio/project/delete-project.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
          console.log("✅ Delete response:", response);
          if (response.status === 1) {
            showToast("Project deleted successfully!");
            loadProject(userID);
          } else {
            showError(
              "Deletion failed",
              response.message || "Please try again."
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("❌ AJAX Error:", {
            status: status,
            error: error,
            responseText: xhr.responseText,
            statusCode: xhr.status
          });
          
          // แสดง response จาก server (ถ้ามี)
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            showError("An error occurred", errorResponse.message || "Could not delete Project.");
          } catch (e) {
            showError("An error occurred", "Could not delete Project. Check console for details.");
          }
        },
      });
    }
  });
}
// function deleteProjectItem(itemId, container) {
//   const userID = $("#userID").val();

//   console.log("🔍 Deleting Project:", {
//     itemId: itemId,
//     userID: userID,
//     itemIdType: typeof itemId
//   });

//   Swal.fire({
//     title: "Confirm deletion?",
//     text: "This action cannot be undone.",
//     icon: "warning",
//     showCancelButton: true,
//     confirmButtonText: "Delete",
//     cancelButtonText: "Cancel",
//     confirmButtonColor: "#ef4444",
//     cancelButtonColor: "#6b7280",
//   }).then((result) => {
//     if (result.isConfirmed) {
//       $.ajax({
//         url: "/portfolio/project/delete-project.php",
//         method: "POST",
//         data: {
//           id: itemId, 
//           userID: userID
//         },
//         dataType: "json",
//         success: function (response) {
//           console.log("✅ Delete response:", response); // Debug
//           if (response.status === 1) {
//             showToast("Project deleted successfully!");
//             loadProject(userID);
//           } else {
//             showError(
//               "Deletion failed",
//               response.message || "Please try again."
//             );
//           }
//         },
//         error: function (xhr, status, error) {
//           console.error("❌ AJAX Error:", {
//             status: status,
//             error: error,
//             response: xhr.responseText
//           });
//           showError(
//             "An error occurred",
//             "Could not delete Project."
//           );
//         },
//       });
//     }
//   });
// }

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







