document.addEventListener("DOMContentLoaded", function () {
    // ====== PERSONAL FORM BLUR ======
    const personalForm = document.getElementById("personalForm");
    if (personalForm) {
        const personalElements = personalForm.querySelectorAll("input, textarea, select");

        personalElements.forEach(element => {
            element.addEventListener("blur", function () {
                const formData = new FormData(personalForm);
                fetch("save_personal.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => console.log("Personal saved:", data))
                .catch(err => console.error("Personal Error:", err));
            });
        });
    }
});