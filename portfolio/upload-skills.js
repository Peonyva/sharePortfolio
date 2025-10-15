// ===== GLOBAL VARIABLES =====
let allSkills = [];
let mySkills = [];
let projectSkills = [];

if (addProjectSkillBtn) {
  addProjectSkillBtn.addEventListener("click", addProjectSkill);
}

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
    const response = await fetch("/portfolio/get-skills.php");

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

    allSkills = data;
    console.log(`Loaded ${allSkills.length} skills from server`);

    return allSkills;
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
    console.warn("Dropdown Skills element not found");
    return;
  }

  dropdownSkills.innerHTML = '<option value="">Choose a skill...</option>';

  allSkills.forEach((skill) => {
    const skillsNum = parseInt(skill.id);
    if (!mySkills.includes(skillsNum)) {
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
  const dropdownProjectSkills = document.getElementById("dropdownProjectSkills");
  if (!dropdownProjectSkills) {
    console.warn("Dropdown Project Skills element not found");
    return;
  }

  dropdownProjectSkills.innerHTML = '<option value="">Choose a skill...</option>';

  allSkills.forEach((skill) => {
    const skillsNum = parseInt(skill.id);
    if (!projectSkills.includes(skillsNum)) {
      const option = document.createElement("option");
      option.value = skillsNum;
      option.textContent = skill.name;
      dropdownProjectSkills.appendChild(option);
    }
  });

  const addBtn = document.getElementById("addProjectSkillBtn");
  if (addBtn) {
    addBtn.disabled = true;
  }
}

// ===== EVENT LISTENERS SETUP =====
function setupEventListeners() {
  const dropdownSkills = document.getElementById("dropdownSkills");
  const dropdownProjectSkills = document.getElementById("dropdownProjectSkills");

  if (dropdownSkills) {
    dropdownSkills.addEventListener("change", handleSkillSelectChange);
  }

  if (dropdownProjectSkills) {
    dropdownProjectSkills.addEventListener("change", handleProjectSkillSelectChange);
  }
}

function handleSkillSelectChange(event) {
  const addBtn = document.getElementById("addSkillBtn");
  if (addBtn) {
    const selectedValue = event.target.value;
    addBtn.disabled = selectedValue === "" || !selectedValue;

    console.log("=== [Skills Dropdown Changed] ===");
    console.log("Selected Skill:", selectedValue || "(none)");
    console.log(
      "Add Skill Button Status:",
      addBtn.disabled ? "Disabled ❌" : "Enabled ✅"
    );
    console.log("=================================");
  }
}

function handleProjectSkillSelectChange(event) {
  const addBtn = document.getElementById("addProjectSkillBtn");
  if (addBtn) {
    const selectedValue = event.target.value;
    addBtn.disabled = selectedValue === "" || !selectedValue;

    console.log("=== [Project Skills Dropdown Changed] ===");
    console.log("Selected Project Skill:", selectedValue || "(none)");
    console.log(
      "Add Project Skill Button Status:",
      addBtn.disabled ? "Disabled ❌" : "Enabled ✅"
    );
    console.log("=========================================");
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

  if (skillsNum && !isNaN(skillsNum) && !mySkills.includes(skillsNum)) {
    mySkills.push(skillsNum);
    updateSkillsDisplay();
    populateSkillsDropdown();
    populateProjectSkillsDropdown();
    updateMySkillsInput();
    console.log("Selected skills:", mySkills);
  } else {
    console.log("Invalid skill ID or skill already selected:", skillsNum);
  }
}

function removeSkill(skillsNum) {
  const skillId = parseInt(skillsNum);
  mySkills = mySkills.filter((id) => parseInt(id) !== skillId);
  updateSkillsDisplay();
  populateSkillsDropdown();
  populateProjectSkillsDropdown();
  updateMySkillsInput();
}

// ===== PROJECT SKILL MANAGEMENT =====
function addProjectSkill() {
  const dropdownProjectSkills = document.getElementById("dropdownProjectSkills");
  if (!dropdownProjectSkills) {
    console.error("Project Skill select element not found");
    return;
  }

  const skillsNum = parseInt(dropdownProjectSkills.value);
  console.log("Adding project skill:", skillsNum);

  if (skillsNum && !isNaN(skillsNum) && !projectSkills.includes(skillsNum)) {
    projectSkills.push(skillsNum);
    updateProjectSkillsDisplay();
    populateProjectSkillsDropdown();
    updateProjectSkillsInput();
    console.log("Selected project skills:", projectSkills);
  } else {
    console.log("Invalid project skill ID or already selected:", skillsNum);
  }
}

function removeProjectSkill(skillsNum) {
  const skillId = parseInt(skillsNum);
  projectSkills = projectSkills.filter((id) => parseInt(id) !== skillId);
  updateProjectSkillsDisplay();
  populateProjectSkillsDropdown();
  updateProjectSkillsInput();
}

// ===== UI UPDATES =====
function updateSkillsDisplay() {
  const box = document.getElementById("mySkillsBox");
  const emptyState = document.getElementById("emptySkillsState");
  const skillsList = document.getElementById("skillsList");
  const skillCount = document.getElementById("skillCount");

  if (!box || !emptyState || !skillsList || !skillCount) return;

  if (mySkills.length > 0) {
    box.style.display = "block";
    emptyState.style.display = "none";
    skillCount.textContent = mySkills.length;

    skillsList.innerHTML = "";
    mySkills.forEach((num) => {
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

function updateProjectSkillsDisplay() {
  const box = document.getElementById("myProjectSkillsBox");
  const emptyState = document.getElementById("emptyProjectSkillsState");
  const skillsList = document.getElementById("projectSkillsList");
  const skillCount = document.getElementById("projectSkillCount");

  if (!box || !emptyState || !skillsList || !skillCount) return;

  if (projectSkills.length > 0) {
    box.style.display = "block";
    emptyState.style.display = "none";
    skillCount.textContent = projectSkills.length;

    skillsList.innerHTML = "";
    projectSkills.forEach((num) => {
      const skillsNum = parseInt(num);
      const skill = allSkills.find((s) => parseInt(s.id) === skillsNum);
      if (skill) {
        const skillTag = createSkillTag(skill, `removeProjectSkill(${skillsNum})`);
        skillsList.appendChild(skillTag);
      }
    });
  } else {
    box.style.display = "none";
    emptyState.style.display = "block";
  }
}

function updateMySkillsInput() {
  const mySkillsInput = document.getElementById("mySkillsInput");
  if (mySkillsInput) {
    mySkillsInput.value = mySkills.join(",");
  }
}

function updateProjectSkillsInput() {
  const myProjectSkillsInput = document.getElementById("myProjectSkillsInput");
  if (myProjectSkillsInput) {
    myProjectSkillsInput.value = projectSkills.join(",");
  }
}

function createSkillTag(skill, onClickHandler) {
  const tag = document.createElement("span");
  tag.className = "skill-tag";
  tag.innerHTML = `
    ${skill.name}
    <button type="button" class="skill-remove" onclick="${onClickHandler}">×</button>
  `;
  return tag;
}
