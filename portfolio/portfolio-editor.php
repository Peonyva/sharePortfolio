<?php $title = "Portfolio Editor"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header-editor.php';  ?>

<!-- Main -->
<div class="container">
    <main class="page-con">
        <h2 class="heading">Portfolio Information</h2>

        <section id="personal" class="personal">
            <form id="personalForm" method="POST" enctype="multipart/form-data">
                <!-- 1 -->
                <div class="content-box">
                    <h2 class="title">
                        <span class="number">1</span>Personal
                    </h2>
                    <div class="grid grid-cols-2">
                        <div class="form-group">
                            <label for="firstname" class="required-label">Firstname :</label>
                            <input type="text" id="firstname" readonly>
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="required-label">Lastname :</label>
                            <input type="text" id="lastname" readonly>
                        </div>

                        <div class="form-group">
                            <label for="datebirth" class="required-label">Date of birth :</label>
                            <input type="date" id="datebirth" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="required-label">Email :</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="required-label">Password :</label>
                            <input type="password" id="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password-confirm" class="required-label">Confirm Password :</label>
                            <input type="password" id="password-confirm" required>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="required-label">Phone :</label>
                            <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" maxlength="10" required>
                        </div>

                        <div class="form-group">
                            <label for="ProfessionalTitle" class="required-label">Professional Title:</label>
                            <input type="text" id="ProfessionalTitle" placeholder="e.g., Full Stack Developer, UI/UX Designer" required>
                        </div>
                        <div class="form-group">
                            <label for="facebook" class="required-label">Facebook Name :</label>
                            <input type="text" id="facebook" required>
                        </div>
                        <div class="form-group">
                            <label for="facebookUrl" class="required-label">Facebook URL :</label>
                            <input type="text" id="facebookUrl" placeholder="https://facebook.com/yourname" required>
                        </div>
                    </div> <!-- grid 2 -->

                    <div class="grid grid-cols-2 mt-24 gap-x-0">
                        <!-- Logo Image Upload -->
                        <div class="form-group">
                            <label for="logoImage" class="required-label">Logo Image :</label>
                            <div class="image-uploader" id="logoImageUploader">
                                <div class="upload-placeholder">
                                    <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('logoImage').click()">Upload Logo</button>
                                    <p>PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                            <input type="file" id="logoImage" name="logoImage" class="file-input" accept="image/*" onchange="handleImageUpload(this, 'logoImageUploader')" required>
                        </div>

                        <!-- Profile Image Upload -->
                        <div class="form-group">
                            <label for="profileImage" class="required-label">Profile Image :</label>
                            <div class="image-uploader" id="profileImageUploader">
                                <div class="upload-placeholder">
                                    <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('profileImage').click()">Upload Profile</button>
                                    <p>PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                            <input type="file" id="profileImage" name="profileImage" class="file-input" accept="image/*" onchange="handleImageUpload(this, 'profileImageUploader')" required>
                        </div>

                        <!-- Cover Image Upload -->
                        <div class="form-group">
                            <label for="coverImage" class="required-label">Cover Image :</label>
                            <div class="image-uploader" id="coverImageUploader">
                                <div class="upload-placeholder">
                                    <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('coverImage').click()">Upload Cover</button>
                                    <p>PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                            <input type="file" id="coverImage" name="coverImage" class="file-input" accept="image/*" onchange="handleImageUpload(this, 'coverImageUploader')" required>
                        </div>
                    </div> <!-- grid 2 -->

                    <div class="form-group">
                        <label for="introContent" class="required-label">About Me :</label>
                        <textarea id="introContent" name="introContent" rows="4" placeholder="Tell us about your professional background, achievements, and career goals." required></textarea>
                        <div class="description-message">Press Enter to separate each item onto a new line.</div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="content-box">
                    <h2 class="title">
                        <span class="number">2</span>Skills
                    </h2>

                    <div class="form-group">
                        <label for="skillsContent" class="required-label">Description my skills :</label>
                        <textarea id="skillsContent" name="skillsContent" rows="4" placeholder="List your technical skills, programming languages, software, and other relevant abilities." required></textarea>
                        <div class="description-message">Press Enter to separate each item onto a new line.</div>
                    </div>

                    <div class="input-group">
                        <div class="form-group dropdown">
                            <label for="dropdownSkills" class="required-label">Select Skill :</label>
                            <select id="dropdownSkills" class="form-select">
                                <option value="">Choose a skill...</option>
                            </select>
                        </div>

                        <div class="btn-wrapper addskills">
                            <button type="button" id="addSkillBtn" class="btn btn-success btn-addSkill" onclick="addSkill()" disabled>
                                Add Skill
                            </button>
                        </div>
                    </div>

                    <div id="mySkillsBox" class="selected-skills">
                        <h5>Selected Skills (<span id="skillCount">0</span>)</h5>
                        <div id="skillsList" class="skills-list"></div>
                    </div>
                    <div id="emptySkillsState" class="empty-state">
                        No skills selected yet. Use the dropdown above to add skills.
                    </div>
                    <input type="hidden" name="mySkills" id="mySkillsInput" />
                </div>
            </form>
        </section>

        <!-- ทดสอบ UserID -->
        <input type="hidden" id="userID" name="userID" value="1">

        <!-- 3 -->
        <div class="content-box">
            <section id="workExperience" class="workExperience">
                <div class="header">
                    <h2 class="title">
                        <span class="number">3</span>Work Experience
                    </h2>
                    <button type="button" class="btn btn-primary btn-toggle" data-target="#AddWorkExp">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <form id="AddWorkExp" class="form-Add-data hidden" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-2">
                        <div class="form-group">
                            <label for="companyName" class="required-label">Company Name :</label>
                            <input type="text" id="companyName" name="companyName" required>
                        </div>
                        <div class="form-group">
                            <label for="employeeType" class="required-label">Employment Type :</label>
                            <select id="employeeType" name="employeeType" class="form-select" required>
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
                            <input type="text" id="position" name="position" required>
                        </div>

                        <div class="form-group">
                            <label for="workStartDate" class="required-label">Start Date :</label>
                            <input type="date" id="workStartDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="workEndDate" class="required-label">End Date :</label>
                            <input type="date" id="workEndDate" name="endDate" required>
                            <div class="error-message">End date must be after start date.</div>
                        </div>
                    </div>

                    <div class="form-checkbox-group">
                        <input type="checkbox" id="workIsCurrent" name="isCurrent" value="1" class="form-checkbox">
                        <label for="workIsCurrent">I currently work here</label>
                    </div>

                    <div class="form-group">
                        <label for="jobDescription" class="required-label">Job Description :</label>
                        <textarea id="jobDescription" name="jobDescription" required></textarea>
                        <div class="description-message">Press Enter to separate each item onto a new line.</div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks :</label>
                        <textarea id="workRemarks" name="remarks"></textarea>
                    </div>

                    <div class="btn-wrapper">
                        <button type="submit" id="btnSaveWorkExp" class="btn btn-success btn-manage">Save</button>
                        <button type="button" id="btnCancelWorkExp" class="btn btn-danger btn-manage">Cancel</button>
                    </div>
                </form>

                <div id="WorkExp"></div>
            </section>
        </div>


        <!-- 4 -->
        <div class="content-box">
            <section id="education" class="education">

                <div class="header">
                    <h2 class="title">
                        <span class="number">4</span>Education
                    </h2>
                    <button type="button" class="btn btn-primary btn-toggle" data-target="#AddEducation">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <form id="AddEducation" class="form-Add-data hidden" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-2">

                        <div class="form-group">
                            <label for="educationName" class="required-label">Education Name :</label>
                            <input type="text" id="educationName" name="educationName" required>
                        </div>
                        <div class="form-group">
                            <label for="degree" class="required-label">Degree :</label>
                            <input type="text" id="degree" name="degree" required>
                        </div>
                        <div class="form-group">
                            <label for="facultyName" class="required-label">Faculty :</label>
                            <input type="text" id="facultyName" name="facultyName" required>
                        </div>
                        <div class="form-group">
                            <label for="majorName" class="required-label">Major :</label>
                            <input type="text" id="majorName" name="majorName" required>
                        </div>
                        <div class="form-group">
                            <label for="eduStartDate" class="required-label">Start Date :</label>
                            <input type="date" id="eduStartDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="eduEndDate" class="required-label">End Date :</label>
                            <input type="date" id="eduEndDate" name="endDate" required>
                            <div class="error-message">End date must be after start date.</div>
                        </div>
                    </div>

                    <div class="form-checkbox-group">
                        <input type="checkbox" id="eduIsCurrent" name="isCurrent" value="1" class="form-checkbox">
                        <label for="eduIsCurrent">Currently studying here</label>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks :</label>
                        <textarea id="eduRemarks" name="remarks"></textarea>
                    </div>

                    <div class="btn-wrapper">
                        <button type="submit" id="btnSaveEducation" class="btn btn-success btn-manage">Save</button>
                        <button type="button" id="btnCancelEducation" class="btn btn-danger btn-manage">Cancel</button>
                    </div>
                </form>
                <div id="Education"></div>
            </section>
        </div>

        <!-- 5 -->
        <div class="content-box">
            <section id="project" class="project">
                <div class="header">
                    <h2 class="title">
                        <span class="number">5</span>Project
                    </h2>
                    <button type="button" class="btn btn-primary btn-toggle" data-target="#AddProject">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <form id="AddProject" class="form-Add-data hidden" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="projectTitle" class="required-label">Project Title :</label>
                        <input type="text" id="projectTitle" name="projectTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="projectImage" class="required-label">Project Image :</label>
                        <div class="image-uploader" id="projectImageUploader">
                            <div class="upload-placeholder">
                                <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('projectImage').click()">
                                    Upload Image
                                </button>
                                <p>PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <input type="file" id="projectImage" name="projectImage" class="file-input" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="keyPoint" class="required-label">Job Description :</label>
                        <textarea id="keyPoint" name="keyPoint" rows="3" placeholder="Describe the project, your role, and key achievements..." required></textarea>
                        <div class="description-message">Press Enter to separate each item onto a new line.</div>
                    </div>
                    <div class="input-group">
                        <div class="form-group dropdown">
                            <label for="dropdownProjectSkills" class="required-label">Select Skill :</label>
                            <select id="dropdownProjectSkills" class="form-select">
                                <option value="">Choose a skill...</option>
                            </select>
                        </div>

                        <div class="btn-wrapper">
                            <button type="button" id="addProjectSkillBtn" class="btn btn-success btn-manage" disabled>
                                Add Skill
                            </button>
                        </div>
                    </div>

                    <div id="myProjectSkillsBox" class="selected-skills">
                        <h5>Selected Skills (<span id="projectSkillCount">0</span>)</h5>
                        <div id="projectSkillsList" class="skills-list"></div>
                    </div>
                    <div id="emptyProjectSkillsState" class="empty-state">
                        No skills selected yet. Use the dropdown above to add skills.
                    </div>
                    <input type="hidden" name="myProjectSkills" id="myProjectSkillsInput" />
                    <div class="btn-wrapper">
                        <button type="submit" id="btnSaveProject" class="btn btn-success btn-manage">Save</button>
                        <button type="button" id="btnCancelProject" class="btn btn-danger btn-manage">Cancel</button>
                    </div>
                </form>
                <div id="Project"></div>
            </section>

        </div>

    </main>
</div>

<!-- Footer -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>

<!-- Scripts -->
<script src="/portfolio/portfolio-editor.js"></script>
<script src="/portfolio/upload-image.js"></script>
<script src="/portfolio/upload-skills.js"></script>

</body>
</html>