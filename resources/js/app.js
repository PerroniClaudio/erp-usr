import "./bootstrap";

// Utility per il refresh del token CSRF
window.refreshCsrfToken = async function () {
    try {
        const response = await fetch("/refresh-csrf-token", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (response.ok) {
            const data = await response.json();
            if (data.csrf_token) {
                // Aggiorna il meta tag
                const metaTag = document.head.querySelector(
                    'meta[name="csrf-token"]'
                );
                if (metaTag) {
                    metaTag.setAttribute("content", data.csrf_token);
                }
                // Aggiorna l'header di Axios
                window.axios.defaults.headers.common["X-CSRF-TOKEN"] =
                    data.csrf_token;
                return data.csrf_token;
            }
        }
    } catch (error) {
        console.error("Error refreshing CSRF token:", error);
    }
    return null;
};

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
