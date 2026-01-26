import axios from "axios";

const updateLabel = (element, hours) => {
    if (!element) return;
    const template = element.dataset.template || ":hours ore";
    const value = Number(hours);
    const formatted = template.replace(
        ":hours",
        Number.isFinite(value) ? value.toFixed(1) : "0.0",
    );
    element.textContent = formatted;
    if (value < 0) {
        element.classList.add("text-error");
    } else {
        element.classList.remove("text-error");
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const containers = document.querySelectorAll("[data-time-off-balance]");
    if (!containers.length) return;

    containers.forEach(async (container) => {
        const endpoint = container.dataset.balanceUrl;
        if (!endpoint) return;

        const ferieEl = container.querySelector('[data-balance="ferie"]');
        const rolEl = container.querySelector('[data-balance="rol"]');
        const warningEl = container.querySelector("[data-balance-warning]");

        try {
            const { data } = await axios.get(endpoint);
            updateLabel(ferieEl, data?.time_off_remaining_hours ?? 0);
            updateLabel(rolEl, data?.rol_remaining_hours ?? 0);
            if (warningEl) {
                warningEl.classList.toggle(
                    "hidden",
                    !data?.is_residual_fallback,
                );
            }
        } catch (error) {
            console.error("Errore nel recupero saldo ferie/rol", error);
        }
    });
});
