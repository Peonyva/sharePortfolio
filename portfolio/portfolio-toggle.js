// portfolio-toggle.js - สำหรับ toggle บนหน้า portfolio.php
$(document).ready(function() {
  const portfolioToggle = $("#portfolioPublishToggle");
  const userID = $("#portfolioUserID").val();
  const statusText = $(".status-text");

  // เมื่อมีการเปลี่ยนสถานะ toggle
  portfolioToggle.on("change", function() {
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
          
          // อัปเดตข้อความสถานะ
          if (isPublic === 1) {
            statusText.text("Public");
            alert("Your portfolio is now public!");
          } else {
            statusText.text("Private");
            alert("Your portfolio is now private!");
          }
        } else {
          console.error("Failed to update public status:", response.message);
          // ย้อนกลับ toggle ถ้าอัปเดตไม่สำเร็จ
          portfolioToggle.prop("checked", !isPublic);
          alert("Failed to update status. Please try again.");
        }
      },
      error: function(xhr, status, error) {
        console.error("Error updating public status:", error);
        // ย้อนกลับ toggle ถ้าเกิดข้อผิดพลาด
        portfolioToggle.prop("checked", !isPublic);
        alert("Error updating status. Please try again.");
      }
    });
  });
});