import axios from "axios";

let chartInstance = null;
let chartModulePromise = null;

function debounce(fn, delay = 400) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("add_time_off_modal");
    if (!modal) {
        return;
    }

    const endpoint = modal.dataset.calculateUrl;
    const storeEndpoint = modal.dataset.storeUrl;
    const userId = modal.dataset.userId;

    if (!endpoint || !userId) {
        return;
    }

    const referenceDateInput = modal.querySelector("#reference-date-input");
    const timeOffAmountInput = modal.querySelector("#time-off-amount-input");
    const rolAmountInput = modal.querySelector("#rol-amount-input");
    const insertDateInput = modal.querySelector('input[name="insert_date"]');

    const timeOffTotalInput = document.getElementById("time-off-total-input");
    const timeOffUsedInput = document.getElementById("time-off-used-input");
    const rolTotalInput = document.getElementById("rol-total-input");
    const rolUsedInput = document.getElementById("rol-used-input");

    const timeOffRemainingLabel = document.getElementById(
        "time-off-remaining-label"
    );
    const rolRemainingLabel = document.getElementById("rol-remaining-label");
    const timeOffRemainingModalLabel = document.getElementById(
        "time-off-remaining-label-modal"
    );
    const rolRemainingModalLabel = document.getElementById(
        "rol-remaining-label-modal"
    );
    const saveButton = document.getElementById("save-time-off-amount");
    const monthFilter = document.getElementById("month-filter");
    const monthEndpoint =
        document.getElementById("time-off-overview")?.dataset.monthUrl;
    const usageEndpoint =
        document.getElementById("time-off-overview")?.dataset.usageUrl;
    const usageCanvas = document.getElementById("time-off-usage-chart");
    const loadChartJs = async () => {
        if (!chartModulePromise) {
            chartModulePromise = import("chart.js/auto");
        }
        return chartModulePromise;
    };

    const updateLabel = (element, hours) => {
        if (!element) return;
        const template = element.dataset.template || ":hours ore";
        const formatted = template.replace(":hours", hours.toFixed(1));
        element.textContent = formatted;
        if (hours < 0) {
            element.classList.add("text-error");
        } else {
            element.classList.remove("text-error");
        }
    };

    const syncTotals = () => {
        if (timeOffTotalInput && timeOffAmountInput?.value) {
            timeOffTotalInput.value = timeOffAmountInput.value;
        }
        if (rolTotalInput && rolAmountInput?.value) {
            rolTotalInput.value = rolAmountInput.value;
        }
    };

    const requestResiduals = async () => {
        if (!referenceDateInput?.value) return;

        syncTotals();

        const payload = {
            user_id: userId,
            reference_date: referenceDateInput.value,
            time_off_amount: parseFloat(timeOffAmountInput?.value || 0),
            rol_amount: parseFloat(rolAmountInput?.value || 0),
        };

        try {
            const response = await axios.post(endpoint, payload);
            const data = response.data;

            if (timeOffUsedInput && data.time_off_used_hours !== undefined) {
                timeOffUsedInput.value = data.time_off_used_hours;
            }

            if (rolUsedInput && data.rol_used_hours !== undefined) {
                rolUsedInput.value = data.rol_used_hours;
            }

            if (
                data.time_off_remaining_hours !== undefined &&
                timeOffRemainingLabel
            ) {
                updateLabel(timeOffRemainingLabel, data.time_off_remaining_hours);
                updateLabel(
                    timeOffRemainingModalLabel,
                    data.time_off_remaining_hours
                );
            }

            if (data.rol_remaining_hours !== undefined && rolRemainingLabel) {
                updateLabel(rolRemainingLabel, data.rol_remaining_hours);
                updateLabel(rolRemainingModalLabel, data.rol_remaining_hours);
            }
        } catch (error) {
            console.error("Errore nel calcolo residui time-off/ROL", error);
        }
    };

    const fetchResiduals = debounce(requestResiduals, 400);

    const renderUsageChart = async (chartData) => {
        if (!usageCanvas || !chartData) return;
        const { Chart } = await loadChartJs();

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(usageCanvas.getContext("2d"), {
            type: "line",
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: "Monte Ferie",
                        data: chartData.ferie_amounts || [],
                        borderColor: "#dc2626",
                        backgroundColor: "rgba(220, 38, 38, 0.12)",
                        tension: 0.2,
                        fill: true,
                        borderDash: [6, 4],
                    },
                    {
                        label: "Monte ROL",
                        data: chartData.rol_amounts || [],
                        borderColor: "#1d4ed8",
                        backgroundColor: "rgba(29, 78, 216, 0.12)",
                        tension: 0.2,
                        fill: true,
                        borderDash: [6, 4],
                    },
                    {
                        label: "Ferie",
                        data: chartData.ferie || [],
                        borderColor: "#ef4444",
                        backgroundColor: "rgba(239, 68, 68, 0.2)",
                        tension: 0.2,
                        fill: true,
                    },
                    {
                        label: "ROL",
                        data: chartData.rol || [],
                        borderColor: "#3b82f6",
                        backgroundColor: "rgba(59, 130, 246, 0.2)",
                        tension: 0.2,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "top",
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `${value}h`,
                        },
                    },
                },
            },
        });
    };

    const fetchUsage = async () => {
        if (!usageEndpoint || !referenceDateInput?.value) return;

        const payload = {
            user_id: userId,
            reference_date: referenceDateInput.value,
        };

        try {
            const { data } = await axios.post(usageEndpoint, payload);
            await renderUsageChart(data);
        } catch (error) {
            console.error("Errore nel recupero del trend mensile", error);
        }
    };

    const updateReferenceDateForMonth = (month) => {
        const baseDate = referenceDateInput?.value
            ? new Date(referenceDateInput.value)
            : new Date();
        const year = baseDate.getFullYear();
        const lastDay = new Date(year, month, 0);
        if (referenceDateInput) {
            referenceDateInput.value = lastDay.toISOString().slice(0, 10);
        }
    };

    const fetchMonthAmounts = async (month) => {
        if (!monthEndpoint || !month || !userId) return;

        const baseDate = referenceDateInput?.value
            ? new Date(referenceDateInput.value)
            : new Date();
        const payload = {
            user_id: userId,
            month: Number(month),
            year: baseDate.getFullYear(),
        };

        try {
            const { data } = await axios.post(monthEndpoint, payload);
            if (timeOffAmountInput && data.time_off_amount !== undefined) {
                timeOffAmountInput.value = data.time_off_amount;
            }
            if (rolAmountInput && data.rol_amount !== undefined) {
                rolAmountInput.value = data.rol_amount;
            }
            syncTotals();
            await requestResiduals();
        } catch (error) {
            console.error("Errore nel recupero del monte mensile", error);
        }
    };

    [referenceDateInput, timeOffAmountInput, rolAmountInput]
        .filter(Boolean)
        .forEach((input) => {
            input.addEventListener("input", fetchResiduals);
            input.addEventListener("change", fetchResiduals);
        });

    if (monthFilter) {
        monthFilter.addEventListener("change", async (e) => {
            const selectedMonth = Number(e.target.value);
            if (!selectedMonth) return;
            updateReferenceDateForMonth(selectedMonth);
            await fetchMonthAmounts(selectedMonth);
            await requestResiduals();
            await fetchUsage();
        });
    }

    if (saveButton && storeEndpoint) {
        saveButton.addEventListener("click", async () => {
            if (
                !insertDateInput?.value ||
                !referenceDateInput?.value ||
                !timeOffAmountInput?.value ||
                !rolAmountInput?.value
            ) {
                console.warn("Compila tutti i campi prima di salvare.");
                return;
            }

            const payload = {
                user_id: userId,
                insert_date: insertDateInput.value,
                reference_date: referenceDateInput.value,
                time_off_amount: parseFloat(timeOffAmountInput.value),
                rol_amount: parseFloat(rolAmountInput.value),
            };

            try {
                await axios.post(storeEndpoint, payload);
                await requestResiduals();
                await fetchUsage();
                modal.close();
            } catch (error) {
                console.error("Errore nel salvataggio del monte ore", error);
            }
        });
    }

    // Prima valorizzazione alla apertura pagina
    const initialMonth = monthFilter ? Number(monthFilter.value) : null;
    if (initialMonth) {
        updateReferenceDateForMonth(initialMonth);
        fetchMonthAmounts(initialMonth).then(() => {
            requestResiduals();
            fetchUsage();
        });
    } else {
        fetchResiduals();
        fetchUsage();
    }
});
