import axios from "axios";

let chartInstance = null;
let chartModulePromise = null;
let overviewReferenceDate = null;

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
    const entryTypeSelect = modal.querySelector("#time-off-entry-type");
    const entryYearSelect = modal.querySelector("#time-off-entry-year");
    const timeOffAmountInput = modal.querySelector("#time-off-amount-input");
    const rolAmountInput = modal.querySelector("#rol-amount-input");
    const insertDateInput = modal.querySelector('input[name="insert_date"]');

    const timeOffTotalInput = document.getElementById("time-off-total-input");
    const timeOffUsedInput = document.getElementById("time-off-used-input");
    const rolTotalInput = document.getElementById("rol-total-input");
    const rolUsedInput = document.getElementById("rol-used-input");

    const timeOffRemainingLabel = document.getElementById(
        "time-off-remaining-label",
    );
    const rolRemainingLabel = document.getElementById("rol-remaining-label");
    const timeOffRemainingModalLabel = document.getElementById(
        "time-off-remaining-label-modal",
    );
    const rolRemainingModalLabel = document.getElementById(
        "rol-remaining-label-modal",
    );
    const saveButton = document.getElementById("save-time-off-amount");
    const monthFilter = document.getElementById("month-filter");
    const yearFilter = document.getElementById("year-filter");
    const timeOffContent = document.getElementById("time-off-content");
    const timeOffLoader = document.getElementById("time-off-loading");
    const searchButton = document.getElementById("time-off-search");
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

    const setFiltersLoading = (isLoading) => {
        if (timeOffContent) {
            timeOffContent.classList.toggle("hidden", isLoading);
            timeOffLoader.classList.toggle("hidden", !isLoading);
            timeOffLoader.classList.toggle("flex", isLoading);
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

    const normalizeReferenceDateByType = () => {
        if (!referenceDateInput || !entryTypeSelect || !entryYearSelect) return;
        const year = Number(entryYearSelect.value);
        if (!year) return;
        if (entryTypeSelect.value === "residual") {
            referenceDateInput.value = `${year}-12-31`;
        } else {
            referenceDateInput.value = `${year}-01-01`;
        }
    };

    const resetEntryForm = () => {
        if (timeOffAmountInput) {
            timeOffAmountInput.value = "";
        }
        if (rolAmountInput) {
            rolAmountInput.value = "";
        }
        if (insertDateInput) {
            insertDateInput.value = new Date().toISOString().slice(0, 10);
        }
        if (entryTypeSelect) {
            entryTypeSelect.value = "total";
        }
        if (entryYearSelect) {
            const fallbackYear = yearFilter?.value
                ? Number(yearFilter.value)
                : new Date().getFullYear();
            entryYearSelect.value = String(fallbackYear);
        }
        normalizeReferenceDateByType();
    };

    const getOverviewReferenceDate = () => {
        if (overviewReferenceDate) {
            return overviewReferenceDate;
        }
        if (referenceDateInput?.value) {
            return referenceDateInput.value;
        }
        return new Date().toISOString().slice(0, 10);
    };

    const requestResiduals = async () => {
        const overviewDate = getOverviewReferenceDate();
        if (!overviewDate) return;

        syncTotals();

        const payload = {
            user_id: userId,
            reference_date: overviewDate,
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
                updateLabel(
                    timeOffRemainingLabel,
                    data.time_off_remaining_hours,
                );
                updateLabel(
                    timeOffRemainingModalLabel,
                    data.time_off_remaining_hours,
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
        const overviewDate = getOverviewReferenceDate();
        if (!usageEndpoint || !overviewDate) return;

        const payload = {
            user_id: userId,
            reference_date: overviewDate,
        };

        try {
            const { data } = await axios.post(usageEndpoint, payload);
            await renderUsageChart(data);
        } catch (error) {
            console.error("Errore nel recupero del trend mensile", error);
        }
    };

    const updateReferenceDateForMonth = (month, yearOverride = null) => {
        const year = yearOverride ?? new Date().getFullYear();
        const lastDay = new Date(year, month, 0);
        overviewReferenceDate = lastDay.toISOString().slice(0, 10);
    };

    const fetchMonthAmounts = async (
        month,
        year,
        { skipResiduals = false } = {},
    ) => {
        if (!monthEndpoint || !month || !year || !userId) return;
        const payload = {
            user_id: userId,
            month: Number(month),
            year: Number(year),
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
            if (!skipResiduals) {
                await requestResiduals();
            }
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

    if (entryTypeSelect && entryYearSelect && referenceDateInput) {
        const handleEntryChange = () => {
            normalizeReferenceDateByType();
        };
        entryTypeSelect.addEventListener("change", handleEntryChange);
        entryYearSelect.addEventListener("change", handleEntryChange);
        normalizeReferenceDateByType();
    }

    const handleFilterChange = async () => {
        const selectedMonth = Number(monthFilter?.value);
        const selectedYear = Number(yearFilter?.value);
        if (!selectedMonth || !selectedYear) return;
        setFiltersLoading(true);
        try {
            updateReferenceDateForMonth(selectedMonth, selectedYear);
            if (entryYearSelect) {
                entryYearSelect.value = String(selectedYear);
                normalizeReferenceDateByType();
            }
            await fetchMonthAmounts(selectedMonth, selectedYear, {
                skipResiduals: true,
            });
            await requestResiduals();
            await fetchUsage();
        } finally {
            setFiltersLoading(false);
        }
    };

    if (searchButton) {
        searchButton.addEventListener("click", handleFilterChange);
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
                resetEntryForm();
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
    const initialYear = yearFilter ? Number(yearFilter.value) : null;
    if (initialMonth && initialYear) {
        setFiltersLoading(true);
        updateReferenceDateForMonth(initialMonth, initialYear);
        if (entryYearSelect) {
            entryYearSelect.value = String(initialYear);
            normalizeReferenceDateByType();
        }
        fetchMonthAmounts(initialMonth, initialYear, { skipResiduals: true })
            .then(async () => {
                await requestResiduals();
                await fetchUsage();
            })
            .finally(() => {
                setFiltersLoading(false);
            });
    } else {
        fetchResiduals();
        fetchUsage();
    }
});
