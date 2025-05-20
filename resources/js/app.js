import "./bootstrap";

const alerts = document.querySelectorAll(".alert");

alerts.forEach((alert) => {
    alert.addEventListener("click", () => {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";
        setTimeout(() => {
            alert.remove();
        }, 200); // Delay by 500 milliseconds to match the fade-out duration
    });
});
