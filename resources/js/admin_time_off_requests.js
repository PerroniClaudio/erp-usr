import axios from "axios";

/** Richiesta ferie admin */

const generateDaysButton = document.getElementById("generate-days");
const dateErrorField = document.getElementById("date-error-field");
const userErrorField = document.getElementById("user-error-field");
const daysTableBody = document.getElementById("days-table-body");
const daysTableRowTemplate = document.getElementById("days-table-row-template");

generateDaysButton?.addEventListener("click", async () => {
    const userId = document.getElementById("user_id")?.value;
    const dateFrom = document.getElementById("date_from")?.value;
    const dateTo = document.getElementById("date_to")?.value;

    if (!userId) {
        displayUserError("Seleziona un utente.");
        return;
    }

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

    clearErrors();
    const daysArray = await generateDateRange(dateFrom, dateTo);
    populateDaysTable(daysArray);

    showQuickActions();
});

function isValidDate(date) {
    return !isNaN(Date.parse(date));
}

function displayError(message) {
    dateErrorField.innerHTML = message;
}

function displayUserError(message) {
    userErrorField.innerHTML = message;
}

function clearErrors() {
    dateErrorField.innerHTML = "";
    userErrorField.innerHTML = "";
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

/** Invia e approva automaticamente */

submitButton?.addEventListener("click", async (event) => {
    const userId = document.getElementById("user_id")?.value;
    const rows = document.querySelectorAll(".day-row");

    if (!userId) {
        event.preventDefault();
        displayUserError("Seleziona un utente.");
        return;
    }

    if (rows.length === 0) {
        event.preventDefault();
        displayError("Non ci sono giorni da inviare.");
        return;
    }

    // Add daisyUI spinner to submit button
    submitButton.disabled = true;
    const originalContent = submitButton.innerHTML;
    submitButton.innerHTML = `<span class="loading loading-spinner loading-sm"></span> Creazione in corso...`;

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
            user_id: userId,
        };
    });

    axios
        .post("/admin/time-off-requests/create", {
            requests: JSON.stringify(data),
        })
        .then((response) => {
            if (response.status === 200) {
                window.location.href = "/admin/time-off-requests";
            }
        })
        .catch((error) => {
            console.error("Error submitting form:", error);

            // Mostra errore all'utente
            if (
                error.response &&
                error.response.data &&
                error.response.data.message
            ) {
                displayError(error.response.data.message);
            } else {
                displayError(
                    "Si è verificato un errore durante la creazione della richiesta."
                );
            }
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalContent;
        });
});
