document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.getElementById('publishToggle');
    const userIDInput = document.getElementById('userID');

    // ✅ ตรวจสอบ Element ที่จำเป็น
    if (!toggleSwitch || !userIDInput) {
        console.error('Required elements not found');
        return;
    }

    toggleSwitch.addEventListener('change', function() {
        const userID = userIDInput.value;
        const newStatus = this.checked ? 1 : 0;

        // ✅ Disable switch ขณะ process
        toggleSwitch.disabled = true;

        const formData = new FormData();
        formData.append('userID', userID);
        formData.append('isPublic', newStatus);

        fetch('/portfolio/toggle-publish.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // ✅ ตรวจสอบ HTTP status
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            toggleSwitch.disabled = false;

            if (data.status === 1) {
                // ✅ ใช้ showNotification แทน alert
                showNotification('success', `Portfolio is now ${newStatus === 1 ? 'published' : 'unpublished'}`);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            toggleSwitch.disabled = false;
            // ✅ Revert state
            toggleSwitch.checked = !toggleSwitch.checked;
            console.error('Error:', error);
            showNotification('error', 'Failed to update status');
        });
    });
});

// ✅ Notification function ที่สวยงาม
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#4caf50' : '#f44336'};
        color: white;
        border-radius: 4px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
