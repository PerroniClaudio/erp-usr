document.addEventListener("DOMContentLoaded", () => {
    const addStepButton = document.getElementById("add_step_button");
    const addStepModal = document.getElementById("add_step_modal");
    const searchButton = document.getElementById("validate-step-address-button");
    const searchInput = document.getElementById("step-address-search-input");
    const saveButton = document.getElementById("save-step-button");
    const errorLabel = document.querySelector(".step-error");
    const vehicleSelect = document.getElementById("vehicle_id");
    const costPerKmInput = document.getElementById("cost_per_km");
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ?? "";

    const searchUrl = addStepModal?.dataset.searchUrl;
    const storeUrl = addStepModal?.dataset.storeUrl;
    const reorderUrl =
        document
            .getElementById("steps_table_body")
            ?.dataset.reorderUrl ?? "";
    const stepsTableBody = document.getElementById("steps_table_body");

    const stepFields = () =>
        document.querySelectorAll(".step-form");

    const clearError = () => {
        if (errorLabel) errorLabel.textContent = "";
    };

    const setError = (message) => {
        if (errorLabel) errorLabel.textContent = message;
    };

    const clearFields = () => {
        stepFields().forEach((field) => {
            field.value = "";
            field.classList.remove("input-success", "input-warning");
            field.setAttribute("disabled", "disabled");
        });
    };

    const enableFields = () => {
        stepFields().forEach((field) => field.removeAttribute("disabled"));
    };

    const setFieldValue = (selector, value) => {
        const field = document.querySelector(selector);
        if (!field) return;
        field.value = value ?? "";
        field.classList.toggle("input-success", Boolean(value));
        field.classList.toggle("input-warning", !value);
    };

    const handleSearch = async () => {
        if (!searchUrl || !searchInput) return;
        clearError();

        const params = new URLSearchParams({ address: searchInput.value });

        try {
            const response = await fetch(
                `${searchUrl}?${params.toString()}`,
                { headers: { Accept: "application/json" } }
            );

            if (!response.ok) {
                throw response;
            }

            const data = await response.json();
            const { address_details, latitude, longitude } = data.content;

            setFieldValue('.step-form[name="address"]', address_details.road);
            setFieldValue(
                '.step-form[name="street_number"]',
                address_details.house_number
            );
            setFieldValue(
                '.step-form[name="city"]',
                address_details.city ??
                    address_details.town ??
                    address_details.village
            );
            setFieldValue('.step-form[name="province"]', address_details.county);
            setFieldValue('.step-form[name="zip_code"]', address_details.postcode);
            setFieldValue('.step-form[name="latitude"]', latitude);
            setFieldValue('.step-form[name="longitude"]', longitude);

            enableFields();
        } catch (error) {
            let message =
                "Impossibile recuperare i dati dell'indirizzo. Riprova.";

            if (error instanceof Response) {
                if (error.status === 404) {
                    message =
                        "Indirizzo non trovato. Controlla l'input e riprova.";
                } else if (error.status >= 500) {
                    message = "Errore del server. Riprova più tardi.";
                }
            }

            setError(message);
        }
    };

    const handleSave = async () => {
        if (!storeUrl || !csrfToken) return;

        const payload = {
            address: document.querySelector('.step-form[name="address"]')
                ?.value,
            city: document.querySelector('.step-form[name="city"]')?.value,
            province: document.querySelector('.step-form[name="province"]')
                ?.value,
            zip_code: document.querySelector('.step-form[name="zip_code"]')
                ?.value,
            latitude: document.querySelector('.step-form[name="latitude"]')
                ?.value,
            longitude: document.querySelector('.step-form[name="longitude"]')
                ?.value,
        };

        clearError();

        try {
            const response = await fetch(storeUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw response;
            }

            window.location.reload();
        } catch (error) {
            let message =
                "Impossibile salvare la tappa. Verifica i campi e riprova.";

            if (error instanceof Response && error.status === 422) {
                message = "Compila correttamente i campi obbligatori.";
            }

            setError(message);
        }
    };

    if (addStepButton && addStepModal && typeof addStepModal.showModal === "function") {
        addStepButton.addEventListener("click", () => {
            clearError();
            clearFields();
            addStepModal.showModal();
        });
    }

    if (searchButton) {
        searchButton.addEventListener("click", handleSearch);
    }

    if (saveButton) {
        saveButton.addEventListener("click", handleSave);
    }

    // Drag & Drop reorder
    const updateDisplayedOrder = () => {
        if (!stepsTableBody) return;
        stepsTableBody
            .querySelectorAll("tr[data-step-id] .step-number")
            .forEach((cell, index) => {
                cell.textContent = index + 1;
            });
    };

    const persistOrder = async () => {
        if (!reorderUrl || !csrfToken || !stepsTableBody) return;
        const order = Array.from(
            stepsTableBody.querySelectorAll("tr[data-step-id]")
        ).map((row) => Number(row.dataset.stepId));

        try {
            const response = await fetch(reorderUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ order }),
            });

            if (!response.ok) {
                throw response;
            }
        } catch (error) {
            console.error("Errore nel salvataggio dell'ordine tappe", error);
        }
    };

    const makeRowsDraggable = () => {
        if (!stepsTableBody) return;

        let draggedRow = null;

        stepsTableBody.querySelectorAll("tr[data-step-id]").forEach((row) => {
            row.setAttribute("draggable", "true");

            row.addEventListener("dragstart", () => {
                draggedRow = row;
                row.classList.add("bg-base-200");
            });

            row.addEventListener("dragover", (event) => {
                event.preventDefault();
                const targetRow = event.currentTarget;
                if (!draggedRow || draggedRow === targetRow) return;

                const bounding = targetRow.getBoundingClientRect();
                const offset = bounding.y + bounding.height / 2;
                if (event.clientY - offset > 0) {
                    targetRow.after(draggedRow);
                } else {
                    targetRow.before(draggedRow);
                }
            });

            row.addEventListener("dragend", async () => {
                row.classList.remove("bg-base-200");
                draggedRow = null;
                updateDisplayedOrder();
                await persistOrder();
            });
        });
    };

    makeRowsDraggable();
    updateDisplayedOrder();

    // Aggiorna costo al cambio veicolo
    if (vehicleSelect && costPerKmInput) {
        vehicleSelect.addEventListener("change", () => {
            const price = vehicleSelect.selectedOptions[0]?.dataset.price;
            if (price !== undefined) {
                const numeric = Number(price);
                costPerKmInput.value = Number.isFinite(numeric)
                    ? numeric.toFixed(2)
                    : "";
            }
        });
    }
});
