<?php $title = "Portfolio Editor"; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head & Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header-editor.php';  ?>

<!-- Main -->
<div class="container">
    <main class="page-con">
        <div class="content-box portfolio-editor">
            <h2 class="heading">Portfolio Information</h2>
            <!-- 1 -->
            <section id="personal"  class="personal">
                <form id="personalForm" method="POST" enctype="multipart/form-data">
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
                                    <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('profileImage').click()">Upload Logo</button>
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
                                    <button type="button" class="btn btn-primary upload-image" onclick="document.getElementById('coverImage').click()">Upload Logo</button>
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
            <section id="skills" class="skills">
                <h2 class="title">
                    <span class="number">2</span>Skills
                </h2>
                <div class="form-group">
                    <label for="skillsContent" class="required-label">Description my skills :</label>
                    <textarea id="skillsContent" name="skillsContent" rows="4" placeholder="List your technical skills, programming languages, software, and other relevant abilities." required></textarea>
                </div>

                <div class="input-group">
                    <div class="form-group  dropdown">
                        <label for="dropdownSkills" class="required-label">Select Skill :</label>
                        <select id="dropdownSkills" class="form-select">
                            <option value="">Choose a skill...</option>
                        </select>
                    </div>

                    <div class="btn-wrapper">
                        <button type="button" id="addSkillBtn" class="btn-addskill" onclick="addSkill()" disabled>
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
            </section>

            <!-- 3 -->
            <section id="workExperience" class="workexperience">
                <h2 class="title">
                    <span class="number">3</span>Work Experince
                </h2>


            </section>

            <!-- <button type="submit" class="btn btn-submit">Create account</button> -->
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/scripts/scripts.js"></script>
<script src="/scripts/upload-image.js"></script>
<script src="/scripts/upload-skills.js"></script>
<script src="/scripts/form-blur.js"></script>
<!-- <script src="/scripts/portfolioEditor.js"></script> -->
<!-- Footer -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>

</html>