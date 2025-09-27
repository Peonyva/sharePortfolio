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
                        <label for="datebirth" class="required-label">Professinal Title:</label>
                        <input type="date" id="datebirth" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="required-label">Email :</label>
                        <input type="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="required-label">phone :</label>
                        <input type="text" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="facebook" class="required-label">Facebook Name :</label>
                        <input type="text" id="facebook" required>
                    </div>
                    <div class="form-group">
                        <label for="facebookUrl" class="required-label">Facebook URL :</label>
                        <input type="text" id="facebookUrl" required>
                    </div>
                </div> <!-- grid 2 -->

                <div class="grid grid-cols-2 mt-24">
                    <!-- Logo Image Upload -->
                    <div class="form-group">
                        <label for="logoImage" class="required-label">Logo Image :</label>
                        <div class="image-uploader" id="logoImageUploader">
                            <div class="upload-placeholder">
                                <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('logoImage').click()">Upload Logo</button>
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
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('profileImage').click()">Upload Logo</button>
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
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('coverImage').click()">Upload Logo</button>
                                <p>PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <input type="file" id="coverImage" name="coverImage" class="file-input" accept="image/*" onchange="handleImageUpload(this, 'coverImageUploader')" required>
                    </div>
                </div> <!-- grid 2 -->

                 <div class="form-group">
                        <label for="introContent" class="required-label">About Me :</label>
                        <textarea id="introContent" name="introContent" class="form-textarea" rows="4" placeholder="Tell us about your professional background, achievements, and career goals." required></textarea>
                    </div>


                <button type="submit" class="btn btn-submit">Create account</button>
            </form>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/scripts/upload-image.js"></script>
<!-- <script src="/scripts/portfolioEditor.js"></script> -->
<!-- Footer -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php';  ?>

</html>