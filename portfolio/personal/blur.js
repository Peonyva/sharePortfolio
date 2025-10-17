document.addEventListener('DOMContentLoaded', function () {
    const userID = document.getElementById('userID')?.value?.trim();
    if (!userID) {
        console.error("User ID not found. Auto-save disabled.");
        return;
    }

    // ✅ เพิ่ม firstname, lastname เข้าในรายการ
    const userFields = ['firstname', 'lastname', 'birthdate', 'email', 'password', 'password-confirm'];
    const profileFields = ['phone', 'ProfessionalTitle', 'facebook', 'facebookUrl', 'introContent', 'skillsContent'];
    const imageFields = ['logoImage', 'profileImage', 'coverImage'];

    let changedData = { user: false, profile: false, profileSkill: false, images: false };
    let saveTimer;
    const DEBOUNCE_TIME_MS = 500;

    // --- Add event listeners ---
    function addBlurListener(ids, type) {
        ids.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('blur', () => {
                    changedData[type] = true;
                    validateAndSave();
                });
            }
        });
    }

    function addChangeListener(ids, type) {
        ids.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('change', () => {
                    changedData[type] = true;
                    validateAndSave();
                });
            }
        });
    }

    addBlurListener(userFields, 'user');
    addBlurListener(profileFields, 'profile');
    addChangeListener(imageFields, 'images');

    const skillsList = document.getElementById('skillsList');
    if (skillsList) {
        new MutationObserver(() => {
            changedData.profileSkill = true;
            validateAndSave();
        }).observe(skillsList, { childList: true, subtree: true });
    }

    // --- Validation Functions ---
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validatePhone(phone) {
        return /^\d{10}$/.test(phone);
    }

    function validateURL(url) {
        if (!url) return true;
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    function validatePassword() {
        const password = document.getElementById('password')?.value || '';
        const confirm = document.getElementById('password-confirm')?.value || '';
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()])[A-Za-z0-9!@#$%^&*()]{8,16}$/;
        
        if (password || confirm) {
            if (!pattern.test(password)) {
                return { isValid: false, message: 'Password must be 8-16 chars with uppercase, lowercase, number, special char.' };
            }
            if (password !== confirm) {
                return { isValid: false, message: 'Passwords do not match.' };
            }
        }
        return { isValid: true };
    }

    // --- Validate + Save (debounced) ---
    function validateAndSave() {
        clearTimeout(saveTimer);
        let errors = [];
        let isValid = true;

        if (changedData.user) {
            // เฉพาะตรวจสอบ password ถ้ามีการเปลี่ยน
            const password = document.getElementById('password')?.value?.trim() || '';
            const passwordConfirm = document.getElementById('password-confirm')?.value?.trim() || '';
            
            if (password || passwordConfirm) {
                const pwCheck = validatePassword();
                if (!pwCheck.isValid) {
                    errors.push(pwCheck.message);
                    isValid = false;
                }
            }

            const email = document.getElementById('email')?.value?.trim();
            if (email && !validateEmail(email)) {
                errors.push('Invalid email format');
                isValid = false;
            }
        }

        if (changedData.profile) {
            const phone = document.getElementById('phone')?.value?.trim();
            if (phone && !validatePhone(phone)) {
                errors.push('Phone must be 10 digits');
                isValid = false;
            }

            const fbUrl = document.getElementById('facebookUrl')?.value?.trim();
            if (fbUrl && !validateURL(fbUrl)) {
                errors.push('Invalid Facebook URL');
                isValid = false;
            }
        }

        if (!isValid) {
            showNotification(errors.join(', '), 'error');
            return;
        }

        if (changedData.user || changedData.profile || changedData.profileSkill || changedData.images) {
            saveTimer = setTimeout(savePersonalData, DEBOUNCE_TIME_MS);
        }
    }

    // --- Save function ---
    function savePersonalData() {
        const formData = new FormData();
        formData.append('action', 'savePersonal');
        formData.append('userID', userID);

        if (changedData.user) {
            userFields.forEach(id => {
                const val = document.getElementById(id)?.value?.trim();
                if (val) formData.append(id, val);
            });
        }

        if (changedData.profile) {
            profileFields.forEach(id => {
                const val = document.getElementById(id)?.value?.trim();
                if (val) formData.append(id, val);
            });
        }

        if (changedData.images) {
            imageFields.forEach(id => {
                const file = document.getElementById(id)?.files[0];
                if (file) formData.append(id, file);
            });
        }

        if (changedData.profileSkill) {
            const skillsInput = document.getElementById('mySkillsInput')?.value;
            if (skillsInput) formData.append('mySkills', skillsInput);
        }

        showNotification('Saving...', 'info');

        fetch('/portfolio/personal/save-personal.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification('Data saved successfully', 'success');
                    changedData = { user: false, profile: false, profileSkill: false, images: false };
                } else {
                    showNotification(data.message || 'Failed to save data', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showNotification('An error occurred while saving', 'error');
            });
    }

    // --- Notification ---
    function showNotification(message, type) {
        document.querySelector('.auto-save-notification')?.remove();
        const notification = document.createElement('div');
        notification.className = `auto-save-notification ${type}`;
        const icons = { success: '✓', error: '✕', info: '⟳' };
        notification.innerHTML = `<span style="font-size:18px;margin-right:6px;">${icons[type] || 'ℹ'}</span>${message}`;
        document.body.appendChild(notification);
        if (type !== 'info') setTimeout(() => { notification.remove(); }, 3000);
    }
});