import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import itLocale from "@fullcalendar/core/locales/it";
import axios from "axios";

/** Calendario */

let calendarEl = document.getElementById("calendar");

if (calendarEl) {
    let calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin],
        initialView: "dayGridMonth",
        locale: itLocale,
        width: "auto",
        contentHeight: "auto",
        handleWindowResize: true,
        events: function (fetchInfo, successCallback, failureCallback) {
            let page = new Date(fetchInfo.start).getMonth() + 1; // Use the month as the page number
            fetch(
                `/standard/time-off-requests/user?page=${page}&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`
            )
                .then((response) => response.json())
                .then((data) => {
                    successCallback(data.events); // Assuming the API returns an `events` array
                })
                .catch((error) => {
                    failureCallback(error);
                });
        },
        eventClick: function (info) {
            window.location.href = `/standard/time-off-requests/${info.event.groupId}/edit`;
        },
    });
    calendar.render();
}

/** Richiesta ferie */

const generateDaysButton = document.getElementById("generate-days");
const dateErrorField = document.getElementById("date-error-field");
const daysTableBody = document.getElementById("days-table-body");
const daysTableRowTemplate = document.getElementById("days-table-row-template");
const dateFromInput = document.getElementById("date_from");
const dateFromWarning = document.getElementById("date-from-warning");

generateDaysButton?.addEventListener("click", async () => {
    const dateFrom = document.getElementById("date_from")?.value;
    const dateTo = document.getElementById("date_to")?.value;

    if (!isValidDate(dateFrom) || !isValidDate(dateTo)) {
        displayError("Inserisci date valide.");
        return;
    }

    if (new Date(dateTo) < new Date(dateFrom)) {
        displayError(
            "La data di fine deve essere successiva alla data di inizio."
        );
        return;
    }

    clearError();
    const daysArray = await generateDateRange(dateFrom, dateTo);
    populateDaysTable(daysArray);

    // generateDaysButton.setAttribute("disabled", "true");
    showQuickActions();
});

/** Warning per date vicine */
function daysFromToday(dateString) {
    if (!dateString) {
        return Infinity;
    }

    const today = new Date();
    // Normalizza orario a mezzanotte per evitare problemi di fuso
    today.setHours(0, 0, 0, 0);
    const d = new Date(dateString);
    d.setHours(0, 0, 0, 0);

    const diffMs = d - today;
    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
    return diffDays;
}

function updateDateFromWarning() {
    if (!dateFromInput || !dateFromWarning) return;

    const diffDays = daysFromToday(dateFromInput.value);

    // Mostra warning solo se la data è oggi o nei prossimi 5 giorni
    if (diffDays >= 0 && diffDays <= 5) {
        dateFromWarning.classList.remove("hidden");
    } else {
        dateFromWarning.classList.add("hidden");
    }
}

// Controllo on-load se il campo ha già un valore (es. old() o edit)
document.addEventListener("DOMContentLoaded", () => {
    updateDateFromWarning();
});

// Aggiorna quando l'utente cambia la data
dateFromInput?.addEventListener("change", () => {
    updateDateFromWarning();
});

function isValidDate(date) {
    return !isNaN(Date.parse(date));
}

function displayError(message) {
    dateErrorField.innerHTML = message;
}

function clearError() {
    dateErrorField.innerHTML = "";
}

async function generateDateRange(start, end) {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const params = new URLSearchParams({
        date_from: startDate.toISOString(),
        date_to: endDate.toISOString(),
    });

    const response = await axios.get(
        `/standard/time-off-requests/estimate-days?${params}`
    );

    if (response.status === 200) {
        return response.data.days;
    }

    return [];
}

function populateDaysTable(daysArray) {
    if (daysTableBody) {
        daysTableBody.innerHTML = "";
    }

    daysArray.forEach((day, index) => {
        if (daysTableRowTemplate) {
            const clone = daysTableRowTemplate.content.cloneNode(true);
            clone
                .querySelector(".day-row")
                ?.setAttribute("data-key", index + 1);
            clone.querySelector('[name="day"]').value = day.day;
            clone.querySelector('[name="start_time"]').value = day.start_time;
            clone.querySelector('[name="end_time"]').value = day.end_time;
            clone.querySelector('[name="total_hours"]').value = day.total_hours;

            daysTableBody?.appendChild(clone);
        }
    });
}

document.addEventListener("keyup", (event) => {
    const target = event.target;

    if (target.matches('[name="start_time"], [name="end_time"]')) {
        const row = target.closest(".day-row");
        const startTime = row.querySelector('[name="start_time"]').value;
        const endTime = row.querySelector('[name="end_time"]').value;

        if (startTime && endTime) {
            const totalHours = calculateTotalHours(startTime, endTime);
            row.querySelector('[name="total_hours"]').value = totalHours;
        }
    }
});

function calculateTotalHours(startTime, endTime) {
    const start = new Date(`1970-01-01T${startTime}:00`);
    const end = new Date(`1970-01-01T${endTime}:00`);

    if (end < start) {
        return 0;
    }

    const diff = (end - start) / (1000 * 60 * 60);
    return diff.toFixed(2);
}

/** Azioni rapide */

let setAsLeaveButton = document.getElementById("set-as-leave");
let setAsVacationButton = document.getElementById("set-as-vacation");
const submitButton = document.getElementById("submit-button");

function showQuickActions() {
    setAsLeaveButton.classList.remove("hidden");
    setAsVacationButton.classList.remove("hidden");
    submitButton.classList.remove("hidden");
}

setAsLeaveButton?.addEventListener("click", () => {
    setAsLeave();
});

setAsVacationButton?.addEventListener("click", () => {
    setAsVacation();
});

function setAsLeave() {
    document.querySelectorAll(".day-row").forEach((row) => {
        row.querySelector('[name="type"]').value = 2;
    });
}

function setAsVacation() {
    document.querySelectorAll(".day-row").forEach((row) => {
        row.querySelector('[name="type"]').value = 1;
    });
}

/** Invia */

submitButton?.addEventListener("click", async (event) => {
    const rows = document.querySelectorAll(".day-row");

    if (rows.length === 0) {
        event.preventDefault();
        displayError("Non ci sono giorni da inviare.");
        return;
    }

    // Add daisyUI spinner to submit button
    submitButton.disabled = true;
    const originalContent = submitButton.innerHTML;
    submitButton.innerHTML = `<span class="loading loading-spinner loading-sm"></span> Invia`;

    const data = Array.from(rows).map((row) => {
        let date_from =
            row.querySelector('[name="day"]').value +
            " " +
            row.querySelector('[name="start_time"]').value +
            ":00";
        let date_to =
            row.querySelector('[name="day"]').value +
            " " +
            row.querySelector('[name="end_time"]').value +
            ":00";

        return {
            date_from: date_from,
            date_to: date_to,
            time_off_type_id: row.querySelector('[name="type"]').value,
        };
    });

    axios
        .post("/standard/time-off-requests", {
            requests: JSON.stringify(data),
        })
        .then((response) => {
            if (response.status === 200) {
                window.location.href = "/standard/time-off-requests";
            }
        })
        .catch((error) => {
            console.error("Error submitting form:", error);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalContent;
        });
});

/** Modifica */

const editButton = document.getElementById("edit-button");

if (editButton) {
    editButton.addEventListener("click", async (event) => {
        const rows = document.querySelectorAll(".day-row");
        const batch_id = document.getElementById("batch_id");
        const batch_id_value = batch_id.value;

        if (rows.length === 0) {
            event.preventDefault();
            displayError("Non ci sono giorni da inviare.");
            return;
        }

        const data = Array.from(rows).map((row) => {
            let date_from =
                row.querySelector('[name="day"]').value +
                " " +
                row.querySelector('[name="start_time"]').value +
                ":00";
            let date_to =
                row.querySelector('[name="day"]').value +
                " " +
                row.querySelector('[name="end_time"]').value +
                ":00";

            return {
                id: row.getAttribute("data-key"),
                date_from: date_from,
                date_to: date_to,
                time_off_type_id: row.querySelector('[name="type"]').value,
            };
        });

        axios
            .post(`/standard/time-off-requests/${batch_id_value}`, {
                requests: JSON.stringify(data),
            })
            .then((response) => {
                if (response.status === 200) {
                    window.location.href = "/standard/time-off-requests";
                } else {
                    let data = response.data;

                    let errorElement = document.querySelector(
                        '.day-row[data-key="' +
                            data.conflicting_request_id +
                            '"]'
                    );

                    errorElement
                        .querySelector('[name="start_time"]')
                        .classList.add("border-red-500");

                    errorElement
                        .querySelector('[name="end_time"]')
                        .classList.add("border-red-500");
                }
            })
            .catch((error) => {
                console.error("Error submitting form:", error);
            });
    });
}
