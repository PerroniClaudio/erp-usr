import "./bootstrap";

const alerts = document.querySelectorAll(".alert");

alerts.forEach((alert) => {
    alert.addEventListener("click", () => {
        setTimeout(() => {
            alert.remove();
        }, 500); // Delay by 200 milliseconds
    });
});
