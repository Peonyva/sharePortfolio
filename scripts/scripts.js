// ฟังก์ชันแสดง Error ด้วย SweetAlert2
async function showError(title, text) {
  return await Swal.fire({
    icon: "error",
    title: title,
    text: text,
    confirmButtonText: "Confirmed",
    confirmButtonColor: "#ef4444",
  });
}

$(document).ready(function () {
  $(".btn-toggle").click(function () {
    const target = $(this).data("target"); // ดึง selector จาก data-target
    $(target).toggleClass("hidden");       // toggle ซ่อน/แสดงฟอร์ม
  });
});
