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
    const response = await fetch("portfolio/get-skills.php");

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