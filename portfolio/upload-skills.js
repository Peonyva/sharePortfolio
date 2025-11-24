// ===== GLOBAL VARIABLES =====
let allSkills = [];
// ตัวแปรสำหรับฟอร์ม Static (Create New & Profile)
let mySkills = [];      // สำหรับ Profile
let projectSkills = []; // สำหรับ Create New Project

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", async function () {
    // รอให้โหลด Skills เสร็จก่อน
    await initializeApp();
});

async function initializeApp() {
    try {
        await loadSkillsFromServer();
        
        // Setup สำหรับฟอร์ม Static (หน้าเว็บปกติ)
        populateSkillsDropdown();        // Profile Dropdown
        populateProjectSkillsDropdown(); // Create Project Dropdown
        setupEventListeners();

        console.log("Skills system initialized successfully");
    } catch (error) {
        console.error("Failed to initialize skills:", error);
    }
}

// ===== DATA LOADING =====
async function loadSkillsFromServer() {
    try {
        const response = await fetch("/portfolio/get-skills.php");
        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
        
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        
        allSkills = Array.isArray(data) ? data : (data.data || []);
        return allSkills;
    } catch (error) {
        console.error("Failed to load skills:", error);
        allSkills = [];
        throw error;
    }
}

// ==========================================
// ===== STATIC FORM MANAGEMENT Only   =====
// (จัดการเฉพาะฟอร์มสร้างใหม่ และ โปรไฟล์)
// ==========================================

function populateSkillsDropdown() {
    // Dropdown สำหรับ Profile (Personal Info)
    const dropdown = document.getElementById("dropdownSkills");
    if (!dropdown) return;

    dropdown.innerHTML = '<option value="">Choose a skill...</option>';
    allSkills.forEach((skill) => {
        const sId = parseInt(skill.id);
        // แสดงเฉพาะอันที่ยังไม่ได้เลือก
        if (!mySkills.includes(sId)) {
            const option = document.createElement("option");
            option.value = sId;
            option.textContent = skill.name;
            dropdown.appendChild(option);
        }
    });

    const addBtn = document.getElementById("addSkillBtn");
    if (addBtn) addBtn.disabled = true;
}

function populateProjectSkillsDropdown() {
    // Dropdown สำหรับ Create New Project
    const dropdown = document.getElementById("dropdownProjectSkills");
    if (!dropdown) return;

    dropdown.innerHTML = '<option value="">Choose a skill...</option>';
    allSkills.forEach((skill) => {
        const sId = parseInt(skill.id);
        // แสดงเฉพาะอันที่ยังไม่ได้เลือก
        if (!projectSkills.includes(sId)) {
            const option = document.createElement("option");
            option.value = sId;
            option.textContent = skill.name;
            dropdown.appendChild(option);
        }
    });

    const addBtn = document.getElementById("addProjectSkillBtn");
    if (addBtn) addBtn.disabled = true;
}

function setupEventListeners() {
    // Listeners สำหรับ Static Elements เท่านั้น
    const dropdownSkills = document.getElementById("dropdownSkills");
    const dropdownProjectSkills = document.getElementById("dropdownProjectSkills");
    const addSkillBtn = document.getElementById("addSkillBtn");
    const addProjectSkillBtn = document.getElementById("addProjectSkillBtn");

    if (dropdownSkills) dropdownSkills.addEventListener("change", (e) => handleSelectChange(e, "addSkillBtn"));
    if (dropdownProjectSkills) dropdownProjectSkills.addEventListener("change", (e) => handleSelectChange(e, "addProjectSkillBtn"));
    
    if (addSkillBtn) addSkillBtn.addEventListener("click", addProfileSkill);
    if (addProjectSkillBtn) addProjectSkillBtn.addEventListener("click", addCreateProjectSkill);
}

function handleSelectChange(event, btnId) {
    const btn = document.getElementById(btnId);
    if (btn) btn.disabled = !event.target.value;
}

// --- Profile Skills Logic ---
function addProfileSkill() {
    const dropdown = document.getElementById("dropdownSkills");
    if (!dropdown) return;
    const val = parseInt(dropdown.value);
    
    if (val && !mySkills.includes(val)) {
        mySkills.push(val);
        updateSkillsDisplay();
        populateSkillsDropdown();
        updateMySkillsInput();
    }
}

function removeSkill(skillsNum) { // เรียกจาก onclick ใน HTML
    mySkills = mySkills.filter((id) => id !== parseInt(skillsNum));
    updateSkillsDisplay();
    populateSkillsDropdown();
    updateMySkillsInput();
}

function updateSkillsDisplay() {
    const list = document.getElementById("skillsList");
    const count = document.getElementById("skillCount");
    const box = document.getElementById("mySkillsBox");
    const empty = document.getElementById("emptySkillsState");
    
    if (!list) return;

    if (mySkills.length > 0) {
        if(box) box.style.display = "block";
        if(empty) empty.style.display = "none";
        if(count) count.textContent = mySkills.length;
        
        list.innerHTML = "";
        mySkills.forEach(num => {
            const skill = allSkills.find(s => parseInt(s.id) === num);
            if(skill) list.appendChild(createStaticSkillTag(skill, `removeSkill(${num})`));
        });
    } else {
        if(box) box.style.display = "none";
        if(empty) empty.style.display = "block";
    }
}

function updateMySkillsInput() {
    const input = document.getElementById("mySkillsInput");
    if (input) input.value = mySkills.join(",");
}

// --- Create New Project Skills Logic ---
function addCreateProjectSkill() {
    const dropdown = document.getElementById("dropdownProjectSkills");
    if (!dropdown) return;
    const val = parseInt(dropdown.value);

    if (val && !projectSkills.includes(val)) {
        projectSkills.push(val);
        updateProjectSkillsDisplay();
        populateProjectSkillsDropdown();
        updateProjectSkillsInput();
    }
}

function removeProjectSkill(skillsNum) { // เรียกจาก onclick ใน HTML
    projectSkills = projectSkills.filter((id) => id !== parseInt(skillsNum));
    updateProjectSkillsDisplay();
    populateProjectSkillsDropdown();
    updateProjectSkillsInput();
}

function updateProjectSkillsDisplay() {
    const list = document.getElementById("projectSkillsList");
    const count = document.getElementById("projectSkillCount");
    const box = document.getElementById("myProjectSkillsBox");
    const empty = document.getElementById("emptyProjectSkillsState");

    if (!list) return;

    if (projectSkills.length > 0) {
        if(box) box.style.display = "block";
        if(empty) empty.style.display = "none";
        if(count) count.textContent = projectSkills.length;

        list.innerHTML = "";
        projectSkills.forEach(num => {
            const skill = allSkills.find(s => parseInt(s.id) === num);
            if(skill) list.appendChild(createStaticSkillTag(skill, `removeProjectSkill(${num})`));
        });
    } else {
        if(box) box.style.display = "none";
        if(empty) empty.style.display = "block";
    }
}

function updateProjectSkillsInput() {
    const input = document.getElementById("myProjectSkillsInput");
    if (input) input.value = projectSkills.join(",");
}

// Helper สร้าง Tag HTML
function createStaticSkillTag(skill, onClickHandler) {
    const tag = document.createElement("span");
    tag.className = "skill-tag";
    tag.innerHTML = `${skill.name} <button type="button" class="skill-remove" onclick="${onClickHandler}">×</button>`;
    return tag;
}