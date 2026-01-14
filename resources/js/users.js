import axios from "axios";

const user_id = document.querySelector("#user_id").value;

// Configura la ricerca dell'indirizzo associando input, pulsante e classe del form
const setupAddressSearch = (inputId, buttonId, formClass, errorSelector) => {
    const addressInput = document.getElementById(inputId);
    const addressSearch = document.getElementById(buttonId);
    const errorLabel = document.querySelector(errorSelector);

    // Rimuove tutti i messaggi di errore visualizzati
    const clearErrorMessages = () => {
        if (errorLabel) {
            errorLabel.textContent = "";
        }
    };

    // Pulisce i campi dell'indirizzo nel form
    const clearAddressFields = () => {
        document.querySelectorAll(`.${formClass}`).forEach((field) => {
            if (field.tagName === "INPUT" || field.tagName === "TEXTAREA") {
                field.value = "";
            }
        });
    };

    // Imposta il valore di un campo specifico e aggiorna il suo stato visivo
    const setFieldValue = (selector, value) => {
        const field = document.querySelector(selector);
        if (field) {
            if (value !== undefined && value !== null) {
                field.value = value;
                field.classList.remove("input-warning");
                field.classList.add("input-success");
            } else {
                field.value = "";
                field.classList.add("input-warning");
                field.classList.remove("input-success");
            }
        }
    };

    // Abilita i campi dell'indirizzo nel form
    const enableAddressFields = () => {
        document.querySelectorAll(`.${formClass}`).forEach((field) => {
            field.removeAttribute("disabled");
        });
    };

    // Gestisce la risposta di successo del server e popola i campi del form
    const handleSuccessResponse = (data) => {
        const { address_details, latitude, longitude } = data.content;

        setFieldValue(`.${formClass}[name="address"]`, address_details.road);
        setFieldValue(
            `.${formClass}[name="street_number"]`,
            address_details.house_number
        );
        setFieldValue(
            `.${formClass}[name="city"]`,
            address_details.city !== undefined
                ? address_details.city
                : address_details.town !== undefined
                ? address_details.town
                : address_details.village !== undefined
                ? address_details.village
                : ""
        );
        setFieldValue(`.${formClass}[name="province"]`, address_details.county);
        setFieldValue(
            `.${formClass}[name="postal_code"]`,
            address_details.postcode
        );
        setFieldValue(`.${formClass}[name="latitude"]`, latitude);
        setFieldValue(`.${formClass}[name="longitude"]`, longitude);

        enableAddressFields();
    };

    // Gestisce la risposta di errore del server e mostra un messaggio di errore appropriato
    const handleErrorResponse = (error) => {
        if (!errorLabel) return;

        if (error.response) {
            switch (error.response.status) {
                case 404:
                    errorLabel.textContent =
                        "Indirizzo non trovato. Controlla l'input e riprova.";
                    break;
                case 500:
                    errorLabel.textContent =
                        "Errore del server. Riprova più tardi.";
                    break;
                default:
                    errorLabel.textContent =
                        "Impossibile recuperare i dati dell'indirizzo. Riprova.";
            }
        } else {
            errorLabel.textContent = "Errore sconosciuto. Riprova.";
        }
    };

    // Aggiunge un listener al pulsante per avviare la ricerca dell'indirizzo
    if (addressSearch && addressInput) {
        addressSearch.addEventListener("click", () => {
            const params = new URLSearchParams({ address: addressInput.value });

            axios
                .get(`/admin/personnel/users/search-address?${params}`)
                .then((response) => {
                    clearErrorMessages();
                    clearAddressFields();
                    handleSuccessResponse(response.data);
                })
                .catch(handleErrorResponse);
        });
    }
};

// Configura la ricerca per il form di residenza
setupAddressSearch(
    "address-search-input",
    "validate-address-button",
    "residence-form",
    ".residence-error"
);

// Configura la ricerca per il form del recapito
setupAddressSearch(
    "location-address-search-input",
    "validate-location-address-button",
    "location-form",
    ".location-error"
);

const submitButtonResidence = document.getElementById(
    "submit-button-residence"
);
const submitButtonLocation = document.getElementById("submit-button-location");

// Aggiunge un listener al pulsante di invio per il form di residenza
submitButtonResidence.addEventListener("click", (event) => {
    const fd = new FormData();
    const element = document.querySelectorAll(".residence-form");

    element.forEach((el) => {
        if (el.tagName === "INPUT" || el.tagName === "SELECT") {
            fd.append(el.name, el.value);
        }
    });

    axios
        .post(`/admin/personnel/users/${user_id}/store-residence`, fd)
        .then((response) => {
            if (response.data.status === "success") {
                window.location.reload();
            } else {
                console.error(response.data.message);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
        });
});

// Aggiunge un listener al pulsante di invio per il form del recapito
submitButtonLocation.addEventListener("click", (event) => {
    const fd = new FormData();
    const element = document.querySelectorAll(".location-form");

    fd.append(
        "location_address",
        document.querySelector(".location-form[name='address']").value
    );
    fd.append(
        "location_street_number",
        document.querySelector(".location-form[name='street_number']").value
    );
    fd.append(
        "location_city",
        document.querySelector(".location-form[name='city']").value
    );
    fd.append(
        "location_province",
        document.querySelector(".location-form[name='province']").value
    );
    fd.append(
        "location_postal_code",
        document.querySelector(".location-form[name='postal_code']").value
    );
    fd.append(
        "location_latitude",
        document.querySelector(".location-form[name='latitude']").value
    );
    fd.append(
        "location_longitude",
        document.querySelector(".location-form[name='longitude']").value
    );

    axios
        .post(`/admin/personnel/users/${user_id}/store-location`, fd)
        .then((response) => {
            if (response.data.status === "success") {
                window.location.reload();
            } else {
                console.error(response.data.message);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
        });
});

// Abilita e disabilita i campi del form dati personali
const personalDataActivator = document.querySelector(
    "#enable-edit-personal-data"
);

personalDataActivator.addEventListener("click", (event) => {
    const personalDataFields = document.querySelectorAll(
        ".form-input-activable"
    );

    personalDataFields.forEach((field) => {
        if (field.hasAttribute("disabled")) {
            field.removeAttribute("disabled");
        } else {
            field.setAttribute("disabled", "disabled");
        }
    });

    personalDataActivator.classList.toggle("btn-primary");
    personalDataActivator.classList.toggle("btn-secondary");
});

// Gestione calendario di default
const scheduleContainer = document.getElementById("default-schedule-rows");
const scheduleTemplate = document.getElementById("schedule-row-template");
const addScheduleRowButton = document.getElementById("add-schedule-row");

if (scheduleContainer && scheduleTemplate && addScheduleRowButton) {
    let nextScheduleIndex = parseInt(
        scheduleContainer.dataset.nextIndex || "0",
        10
    );

    const addEmptyState = () => {
        if (scheduleContainer.querySelector(".schedule-row")) {
            return;
        }

        const empty = document.createElement("div");
        empty.className = "text-sm text-base-content/70 schedule-empty-state";
        empty.textContent = "Nessuna fascia configurata.";
        scheduleContainer.appendChild(empty);
    };

    const bindRemove = (row) => {
        row.querySelectorAll(".remove-schedule-row").forEach((button) => {
            button.addEventListener("click", () => {
                row.remove();
                addEmptyState();
            });
        });
    };

    const removeEmptyState = () => {
        const emptyState = scheduleContainer.querySelector(
            ".schedule-empty-state"
        );

        if (emptyState) {
            emptyState.remove();
        }
    };

    const addRow = () => {
        removeEmptyState();
        const fragment = scheduleTemplate.content.cloneNode(true);
        const row = fragment.querySelector(".schedule-row");

        fragment.querySelectorAll("[data-name]").forEach((element) => {
            const name = element.dataset.name.replace(
                "__INDEX__",
                nextScheduleIndex
            );
            element.setAttribute("name", name);
        });

        nextScheduleIndex += 1;
        bindRemove(row);
        scheduleContainer.appendChild(fragment);
    };

    scheduleContainer
        .querySelectorAll(".schedule-row")
        .forEach((row) => bindRemove(row));

    addScheduleRowButton.addEventListener("click", () => addRow());
}

const functionSections = document.querySelectorAll("[data-function-section]");
const functionMenuItems = document.querySelectorAll("[data-function-target]");
const drawerToggle = document.getElementById("user-drawer");

if (functionSections.length && functionMenuItems.length) {
    const activateSection = (sectionId) => {
        let targetSection = null;

        functionSections.forEach((section) => {
            const isActive = section.dataset.functionSection === sectionId;
            section.classList.toggle("hidden", !isActive);
            if (isActive) {
                targetSection = section;
            }
        });

        functionMenuItems.forEach((menuItem) => {
            const isActive = menuItem.dataset.functionTarget === sectionId;
            menuItem.classList.toggle("btn-primary", isActive);
            menuItem.classList.toggle("btn-ghost", !isActive);
            menuItem.setAttribute("aria-current", isActive ? "page" : "false");
        });

        if (drawerToggle && window.innerWidth < 1024) {
            drawerToggle.checked = false;
        }

        if (targetSection) {
            window.dispatchEvent(
                new CustomEvent("user-section-change", {
                    detail: { section: targetSection },
                })
            );
        }
    };

    const defaultSectionId =
        functionMenuItems[0]?.dataset.functionTarget ||
        functionSections[0].dataset.functionSection;

    if (defaultSectionId) {
        activateSection(defaultSectionId);
    }

    functionMenuItems.forEach((menuItem) => {
        menuItem.addEventListener("click", () =>
            activateSection(menuItem.dataset.functionTarget)
        );
    });
}
