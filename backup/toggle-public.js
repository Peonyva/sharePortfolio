// toggle-public.js
$(document).ready(function() {
  const publishToggle = $("#publishToggle");
  const userID = $("#userID").val();

  // เมื่อมีการเปลี่ยนสถานะ toggle
  publishToggle.on("change", function() {
    const isPublic = this.checked ? 1 : 0;

    $.ajax({
      url: "/portfolio/update-public-status.php",
      method: "POST",
      data: {
        userID: userID,
        isPublic: isPublic
      },
      dataType: "json",
      success: function(response) {
        if (response.status === 1) {
          console.log("Public status updated successfully");
          
          // ถ้าเปิดเผยแพร่ครั้งแรก (isEverPublic เปลี่ยนจาก 0 เป็น 1)
          if (response.isEverPublic === 1 && response.justPublished === true) {
            alert("Portfolio published successfully! Redirecting to your portfolio page...");
            setTimeout(() => {
              window.location.href = "/portfolio/portfolio.php?user=" + encodeURIComponent(userID);
            }, 1000);
          }
        } else {
          console.error("Failed to update public status:", response.message);
          // ย้อนกลับ toggle ถ้าอัปเดตไม่สำเร็จ
          publishToggle.prop("checked", !isPublic);
        }
      },
      error: function(xhr, status, error) {
        console.error("Error updating public status:", error);
        // ย้อนกลับ toggle ถ้าเกิดข้อผิดพลาด
        publishToggle.prop("checked", !isPublic);
      }
    });
  });
});