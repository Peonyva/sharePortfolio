//  INSERT ROUND 1 

// ===== IMAGE UPLOAD FUNCTIONS =====
function handleImageUpload(input, divPreviewID) {
  const uploader = document.getElementById(divPreviewID);
  const file = input.files[0];

  if (!file) return;
  if (!isValidImageFile(file)) {
    showError("Invalid file", "Please select an image file (JPG, PNG, GIF) no larger than 10 MB.");
    input.value = "";
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    uploader.innerHTML = createImagePreviewHTML(e.target.result, input.id, divPreviewID, file.name);
  };
  reader.onerror = function () {
    showError("An error occurred.", "Cannot read file.");
    input.value = "";
  };
  reader.readAsDataURL(file);
}

function removeImage(divPreviewID, inputFileID) {
  const uploader = document.getElementById(divPreviewID);
  const input = document.getElementById(inputFileID);

  if (input) input.value = "";
  if (uploader) {
    uploader.innerHTML = returnImageValueHTML(inputFileID);
  }
}



// ===== HELPER FUNCTIONS =====
function isValidImageFile(file) {
  if (!file) return false;

  const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
  const maxSize = 10 * 1024 * 1024; // 10MB

  return validTypes.includes(file.type) && file.size <= maxSize;
}

function createImagePreviewHTML(imageSrc, inputFileID, divPreviewID, fileName = "") {
  return `
    <div class="image-preview">
        <img src="${imageSrc}" alt="Preview">
        ${fileName ? `<p>${fileName}</p>` : ""}
    </div>
    <div class="preview-actions">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('${inputFileID}').click()">Change Image</button>
        <button type="button" class="btn btn-danger" onclick="removeImage('${divPreviewID}', '${inputFileID}')">Remove</button>
    </div>
  `;
}

function returnImageValueHTML(inputFileID) {
  return `
        <div class="upload-placeholder">
            <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('${inputFileID}').click()">
                Upload Image
            </button>
            <p class="upload-hint">PNG, JPG, GIF up to 10MB</p>
        </div>
    `;
}

async function showError(title, text) {
  return await Swal.fire({
    icon: "error",
    title: title,
    text: text,
    confirmButtonText: "Confirmed",
    confirmButtonColor: "#ef4444",
  });
}


//  INSERT ROUND 2


// ===== GLOBAL VARIABLES =====
let allSkills = [];
let selectedSkills = [];
let projectSkills = [];

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", async function () {
  await initializeApp();
});

async function initializeApp() {
  try {
    await loadSkillsFromServer();
    populateSkillsDropdown();
    populateProjectSkillsDropdown();
    setupEventListeners();
    
    console.log("Application initialized successfully");
  } catch (error) {
    console.error("Failed to initialize application:", error);
    await showError(
      "Initialization Error",
      "Unable to load skills data. Please refresh the page."
    );
  }
}

// ===== DATA LOADING =====
async function loadSkillsFromServer() {
  try {
    const response = await fetch("get-skills.php");

    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();

    if (data.error) {
      throw new Error(data.error);
    }

    if (!Array.isArray(data)) {
      throw new Error("Invalid data format received from server");
    }

    // Fix: Set allSkills first, then log
    allSkills = data;
    console.log(`Loaded ${allSkills.length} skills from server`);
    
    return allSkills; // Return allSkills instead of data for consistency
  } catch (error) {
    console.error("Failed to load skills from server:", error);
    await showError(
      "Error Loading Skills",
      `Unable to load skills data: ${error.message}`
    );
    allSkills = [];
    throw error;
  }
}

// ===== DROPDOWN POPULATION =====
function populateSkillsDropdown() {
  const dropdownSkills = document.getElementById("dropdownSkills");
  if (!dropdownSkills) {
    console.warn("dropdownSkills element not found");
    return;
  }

  dropdownSkills.innerHTML = '<option value="">Choose a skill...</option>';

  allSkills.forEach((skill) => {
    const skillsNum = parseInt(skill.id);
    if (!selectedSkills.includes(skillsNum)) {
      const option = document.createElement("option");
      option.value = skillsNum;
      option.textContent = skill.name;
      dropdownSkills.appendChild(option);
    }
  });

  const addBtn = document.getElementById("addSkillBtn");
  if (addBtn) {
    addBtn.disabled = true;
  }
}

function populateProjectSkillsDropdown() {
  const projectSelects = document.querySelectorAll(".dropdownProjectSkills");

  projectSelects.forEach((select) => {
    const projectIndex = parseInt(select.dataset.project);
    const myskillsProject = projectSkills[projectIndex] || [];

    select.innerHTML = '<option value="">Choose a skill...</option>';

    allSkills.forEach((skill) => {
      const skillsNum = parseInt(skill.id);
      if (!myskillsProject.includes(skillsNum)) {
        const option = document.createElement("option");
        option.value = skillsNum;
        option.textContent = skill.name;
        select.appendChild(option);
      }
    });

    const addButton = select
      .closest(".input-group")
      .querySelector(".btn-success");
    if (addButton) {
      addButton.disabled = true;
    }
  });
}

// ===== EVENT LISTENERS SETUP =====
function setupEventListeners() {
  // Main skill select
  const dropdownSkills = document.getElementById("dropdownSkills");
  if (dropdownSkills) {
    dropdownSkills.addEventListener("change", handleSkillSelectChange);
  }
  
  // Project skill selects (event delegation)
  document.addEventListener("change", handleProjectSkillSelectChange);

  // Date validation
  setupDateValidation();

  // Current job/education checkboxes
  setupCurrentCheckboxes();
}

// ===== EVENT HANDLERS =====
function handleSkillSelectChange(event) {
  const addBtn = document.getElementById("addSkillBtn");
  if (addBtn) {
    addBtn.disabled = event.target.value === "" || !event.target.value;
    console.log("skill select changed:", event.target.value, "Button disabled:", addBtn.disabled);
  }
}

function handleProjectSkillSelectChange(event) {
  // Fix: Corrected class name from "dropdownProjectSkillst" to "dropdownProjectSkills"
  if (event.target.classList.contains("dropdownProjectSkills")) {
    const projectIndex = parseInt(event.target.dataset.project);
    const addButton = event.target
      .closest(".input-group")
      .querySelector(".btn-success");
    if (addButton) {
      addButton.disabled = event.target.value === "" || !event.target.value;
      console.log(
        `Project ${projectIndex} skill select changed:`,
        event.target.value,
        "Button disabled:",
        addButton.disabled
      );
    }
  }
}

// ===== SKILL MANAGEMENT =====
function addSkill() {
  const dropdownSkills = document.getElementById("dropdownSkills");
  if (!dropdownSkills) {
    console.error("Skill select element not found");
    return;
  }

  const skillsNum = parseInt(dropdownSkills.value); 
  console.log("Adding skill:", skillsNum);

  if (skillsNum && !isNaN(skillsNum) && !selectedSkills.includes(skillsNum)) {
    selectedSkills.push(skillsNum);
    updateSkillsDisplay();
    populateSkillsDropdown();
    populateProjectSkillsDropdown();
    updateSelectedSkillsInput();
    console.log("Selected skills:", selectedSkills);
  } else {
    console.log("Invalid skill ID or skill already selected:", skillsNum);
  }
}

function removeSkill(skillsNum) {
  const skillId = parseInt(skillsNum);
  selectedSkills = selectedSkills.filter((id) => parseInt(id) !== skillId);
  updateSkillsDisplay();
  populateSkillsDropdown();
  populateProjectSkillsDropdown();
  updateSelectedSkillsInput();
}

// ===== PROJECT SKILL MANAGEMENT =====
function addProjectSkill(projectIndex) {
  const select = document.querySelector(`.dropdownProjectSkills[data-project="${projectIndex}"]`);
  if (!select) {
    console.error(`Project skill select for project ${projectIndex} not found`);
    return;
  }

  const skillId = parseInt(select.value);
  console.log(`Adding skill ${skillId} to project ${projectIndex}`);

  if (skillId && !isNaN(skillId)) {
    // Initialize project skills array if it doesn't exist
    if (!projectSkills[projectIndex]) {
      projectSkills[projectIndex] = [];
    }

    // Add skill if not already present
    if (!projectSkills[projectIndex].includes(skillId)) {
      projectSkills[projectIndex].push(skillId);
      updateProjectSkillsDisplay(projectIndex);
      populateProjectSkillsDropdown();
      updateProjectSkillsInput(projectIndex);
      console.log(`Project ${projectIndex} skills:`, projectSkills[projectIndex]);
    } else {
      console.log(`Skill ${skillId} already selected for project ${projectIndex}`);
    }
  }
}

function removeProjectSkill(projectIndex, skillId) {
  if (projectSkills[projectIndex]) {
    projectSkills[projectIndex] = projectSkills[projectIndex].filter(id => parseInt(id) !== parseInt(skillId));
    updateProjectSkillsDisplay(projectIndex);
    populateProjectSkillsDropdown();
    updateProjectSkillsInput(projectIndex);
  }
}

// ===== UI UPDATES =====
function updateSkillsDisplay() {
  const box = document.getElementById("mySkillsBox");
  const emptyState = document.getElementById("emptySkillsState");
  const skillsList = document.getElementById("skillsList");
  const skillCount = document.getElementById("skillCount");

  if (!box || !emptyState || !skillsList || !skillCount) return;

  if (selectedSkills.length > 0) {
    box.style.display = "block";
    emptyState.style.display = "none";
    skillCount.textContent = selectedSkills.length;

    skillsList.innerHTML = "";
    selectedSkills.forEach((num) => {
      const skillsNum = parseInt(num);
      const skill = allSkills.find((s) => parseInt(s.id) === skillsNum);
      if (skill) {
        const skillTag = createSkillTag(skill, `removeSkill(${skillsNum})`);
        skillsList.appendChild(skillTag);
      }
    });
  } else {
    box.style.display = "none";
    emptyState.style.display = "block";
  }
}

function updateProjectSkillsDisplay(projectIndex) {
  const container = document.querySelector(`.project-skills-container[data-project="${projectIndex}"]`);
  const emptyState = document.querySelector(`.project-skills-empty[data-project="${projectIndex}"]`);
  
  if (!container || !emptyState) return;

  const skillsList = container.querySelector('.project-skills-list');
  const skillCount = container.querySelector('.project-skill-count');
  
  if (!skillsList || !skillCount) return;

  const projectSkillsList = projectSkills[projectIndex] || [];

  if (projectSkillsList.length > 0) {
    container.style.display = "block";
    emptyState.style.display = "none";
    skillCount.textContent = projectSkillsList.length;

    skillsList.innerHTML = "";
    projectSkillsList.forEach((skillId) => {
      const skill = allSkills.find((s) => parseInt(s.id) === parseInt(skillId));
      if (skill) {
        const skillTag = createSkillTag(skill, `removeProjectSkill(${projectIndex}, ${skillId})`);
        skillsList.appendChild(skillTag);
      }
    });
  } else {
    container.style.display = "none";
    emptyState.style.display = "block";
  }
}

function updateSelectedSkillsInput() {
  const mySkillsInput = document.getElementById("mySkillsInput");
  if (mySkillsInput) {
    mySkillsInput.value = selectedSkills.join(",");
  }
}

function updateProjectSkillsInput(projectIndex) {
  const input = document.querySelector(`.project-skills-input[name="projects[${projectIndex}][skills]"]`);
  if (input && projectSkills[projectIndex]) {
    input.value = projectSkills[projectIndex].join(",");
  }
}

function createSkillTag(skill, onClickHandler) {
  const tag = document.createElement('span');
  tag.className = 'skill-tag';
  tag.innerHTML = `
    ${skill.name}
    <button type="button" class="skill-remove" onclick="${onClickHandler}">×</button>
  `;
  return tag;
}

// WORK EXPERIENCE & EDUCATION & PROJECT

// ===== DYNAMIC FORM FUNCTIONS =====
let workExperienceCounter = 1;
let educationCounter = 1;
let projectCounter = 1;

//  Date 

function setupDateValidation() {
  document.addEventListener("change", function (e) {
    const container = e.target.closest(".work-item, .education-item");
    if (!container) return;

    if (e.target.type === "date") {
      const startDateInput = container.querySelector('input[name="startDate"]');
      const endDateInput = container.querySelector('input[name="endDate"]');

      if (startDateInput && endDateInput) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value) {
          endDateInput.classList.toggle("input-error", startDate > endDate);
        } else {
          endDateInput.classList.remove("input-error");
        }
      }
    }
  });
}

function setupCurrentCheckboxes() {
  document.addEventListener("change", function (e) {
    const container = e.target.closest(".work-item, .education-item");
    if (!container) return;

    if (e.target.type === "checkbox" && e.target.name === "isCurrent") {
      const endDateInput = container.querySelector('input[name="endDate"]');

      if (endDateInput) {
        if (e.target.checked) {
          endDateInput.value = "";
          endDateInput.disabled = true;
          endDateInput.classList.remove("input-error");
        } else {
          endDateInput.disabled = false;
   
        }
      }
    }
  });
}


// function isValidDate(dateString) {
//   if (!dateString) return false;
//   const date = new Date(dateString);
//   return date instanceof Date && !isNaN(date);
// }

// function isValidDateRange(startDate, endDate) {
//   if (!startDate || !endDate) return true; 
//   const start = new Date(startDate);
//   const end = new Date(endDate);
//   return start <= end;
// }





