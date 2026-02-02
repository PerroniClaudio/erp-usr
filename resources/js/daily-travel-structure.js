document.addEventListener("DOMContentLoaded", () => {
    const addStepButton = document.getElementById("add_step_button");
    const addStepModal = document.getElementById("add_step_modal");
    const searchButton = document.getElementById("validate-step-address-button");
    const searchInput = document.getElementById("step-address-search-input");
    const saveButton = document.getElementById("save-step-button");
    const errorLabel = document.querySelector(".step-error");
    const vehicleSelect = document.getElementById("vehicle_id");
    const costPerKmInput = document.getElementById("cost_per_km");
    const editStepModal = document.getElementById("edit_step_modal");
    const deleteStepModal = document.getElementById("delete_step_modal");
    const editStepInputs = document.querySelectorAll(".edit-step-input");
    const editStepError = document.getElementById("edit-step-error");
    const deleteStepError = document.getElementById("delete-step-error");
    const saveEditStepButton = document.getElementById("save-edit-step-button");
    const confirmDeleteStepButton = document.getElementById(
        "confirm-delete-step-button"
    );
    const editSearchInput = document.getElementById(
        "edit-step-address-search-input"
    );
    const editSearchButton = document.getElementById(
        "edit-validate-step-address-button"
    );
    const timeDifferenceInput = document.getElementById("step_time_difference");
    const editTimeDifferenceInput = document.getElementById(
        "edit_step_time_difference"
    );
    const stepEconomicValueInput = document.getElementById("step_economic_value");
    const editStepEconomicValueInput = document.getElementById(
        "edit_step_economic_value"
    );
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ?? "";

    const searchUrl =
        addStepModal?.dataset.searchUrl ?? editStepModal?.dataset.searchUrl;
    const storeUrl = addStepModal?.dataset.storeUrl;
    const reorderUrl =
        document
            .getElementById("steps_table_body")
            ?.dataset.reorderUrl ?? "";
    const stepsTableBody = document.getElementById("steps_table_body");

    const stepFields = () => document.querySelectorAll(".step-form");
    const editStepFields = () => document.querySelectorAll(".edit-step-input");

    const clearError = (target) => {
        if (target) target.textContent = "";
    };

    const setError = (target, message) => {
        if (target) target.textContent = message;
    };

    const resetFields = (fields) => {
        fields.forEach((field) => {
            field.value = "";
            field.classList.remove("input-success", "input-warning");
            field.setAttribute("disabled", "disabled");
        });
    };

    const enableFields = (fields) => {
        fields.forEach((field) => field.removeAttribute("disabled"));
    };

    const getField = (selector, fieldName) =>
        document.querySelector(
            `${selector}[name="${fieldName}"], ${selector}[data-field="${fieldName}"]`
        );

    const setFieldValue = (selector, fieldName, value) => {
        const field = getField(selector, fieldName);
        if (!field) return;
        field.value = value ?? "";
        field.classList.toggle("input-success", Boolean(value));
        field.classList.toggle("input-warning", !value);
    };

    const getFieldValue = (selector, fieldName) =>
        getField(selector, fieldName)?.value ?? "";

    const normalizeAmount = (value) => {
        const numeric = Number.parseFloat(value ?? "0");
        if (!Number.isFinite(numeric) || numeric < 0) {
            return 0;
        }
        return Number(numeric.toFixed(2));
    };

    const populateAddressFields = (selector, addressDetails, latitude, longitude) => {
        setFieldValue(selector, "address", addressDetails.road);
        setFieldValue(selector, "street_number", addressDetails.house_number);
        setFieldValue(
            selector,
            "city",
            addressDetails.city ?? addressDetails.town ?? addressDetails.village
        );
        setFieldValue(selector, "province", addressDetails.county);
        setFieldValue(selector, "zip_code", addressDetails.postcode);
        setFieldValue(selector, "latitude", latitude);
        setFieldValue(selector, "longitude", longitude);
    };

    const handleAddressSearch = async (inputEl, fieldSelector, errorTarget) => {
        if (!searchUrl || !inputEl) return;
        clearError(errorTarget);

        const params = new URLSearchParams({ address: inputEl.value });

        try {
            const { data } = await axios.get(
                `${searchUrl}?${params.toString()}`,
                { headers: { Accept: "application/json" } }
            );
            const { address_details, latitude, longitude } = data.content;

            populateAddressFields(fieldSelector, address_details, latitude, longitude);
            enableFields(document.querySelectorAll(fieldSelector));
        } catch (error) {
            let message =
                "Impossibile recuperare i dati dell'indirizzo. Riprova.";

            if (axios.isAxiosError(error) && error.response) {
                if (error.response.status === 404) {
                    message =
                        "Indirizzo non trovato. Controlla l'input e riprova.";
                } else if (error.response.status >= 500) {
                    message = "Errore del server. Riprova più tardi.";
                }
            }

            setError(errorTarget, message);
        }
    };

    const handleSave = async () => {
        if (!storeUrl || !csrfToken) return;

        const timeDifferenceValue = Number.parseInt(
            timeDifferenceInput?.value ?? "0",
            10
        );

        const payload = {
            address: getFieldValue(".step-form", "address"),
            city: getFieldValue(".step-form", "city"),
            province: getFieldValue(".step-form", "province"),
            zip_code: getFieldValue(".step-form", "zip_code"),
            latitude: getFieldValue(".step-form", "latitude"),
            longitude: getFieldValue(".step-form", "longitude"),
            time_difference:
                Number.isFinite(timeDifferenceValue) && timeDifferenceValue >= 0
                    ? timeDifferenceValue
                    : 0,
            economic_value: normalizeAmount(stepEconomicValueInput?.value ?? "0"),
        };

        clearError(errorLabel);

        try {
            await axios.post(
                storeUrl,
                payload,
                {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                }
            );

            window.location.reload();
        } catch (error) {
            let message =
                "Impossibile salvare la tappa. Verifica i campi e riprova.";

            if (
                axios.isAxiosError(error) &&
                error.response?.status === 422
            ) {
                message = "Compila correttamente i campi obbligatori.";
            }

            setError(errorLabel, message);
        }
    };

    if (addStepButton && addStepModal && typeof addStepModal.showModal === "function") {
        addStepButton.addEventListener("click", () => {
            clearError(errorLabel);
            resetFields(stepFields());
            if (timeDifferenceInput) {
                timeDifferenceInput.value = "0";
            }
            if (stepEconomicValueInput) {
                stepEconomicValueInput.value = "0";
            }
            addStepModal.showModal();
        });
    }

    if (searchButton) {
        searchButton.addEventListener("click", () =>
            handleAddressSearch(searchInput, ".step-form", errorLabel)
        );
    }

    if (editSearchButton) {
        editSearchButton.addEventListener("click", () =>
            handleAddressSearch(editSearchInput, ".edit-step-input", editStepError)
        );
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
            await axios.post(
                reorderUrl,
                { order },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                }
            );
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

    // Edit/Delete step
    let selectedStepRow = null;

    const openEditModal = (row) => {
        if (!editStepModal) return;
        selectedStepRow = row;
        editStepError.textContent = "";
        resetFields(editStepFields());
        enableFields(editStepFields());

        if (editSearchInput) {
            editSearchInput.value = row.dataset.address ?? "";
        }

        setFieldValue(".edit-step-input", "address", row.dataset.address);
        setFieldValue(".edit-step-input", "city", row.dataset.city);
        setFieldValue(".edit-step-input", "province", row.dataset.province);
        setFieldValue(".edit-step-input", "zip_code", row.dataset.zip);
        setFieldValue(".edit-step-input", "latitude", row.dataset.lat);
        setFieldValue(".edit-step-input", "longitude", row.dataset.lng);

        if (editTimeDifferenceInput) {
            const parsed = Number.parseInt(
                row.dataset.timeDifference ?? "0",
                10
            );
            editTimeDifferenceInput.value =
                Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
        }

        if (editStepEconomicValueInput) {
            const normalizedValue = normalizeAmount(
                row.dataset.economicValue ?? "0"
            );
            editStepEconomicValueInput.value = normalizedValue.toFixed(2);
        }

        editStepModal.showModal();
    };

    const openDeleteModal = (row) => {
        if (!deleteStepModal) return;
        selectedStepRow = row;
        deleteStepError.textContent = "";
        deleteStepModal.showModal();
    };

    const handleStepUpdate = async () => {
        if (!selectedStepRow) return;
        const updateUrl = selectedStepRow.dataset.updateUrl;
        if (!updateUrl || !csrfToken) return;

        editStepError.textContent = "";
        const parsedTimeDifference = Number.parseInt(
            editTimeDifferenceInput?.value ?? "0",
            10
        );

        const payload = {
            address: getFieldValue(".edit-step-input", "address"),
            city: getFieldValue(".edit-step-input", "city"),
            province: getFieldValue(".edit-step-input", "province"),
            zip_code: getFieldValue(".edit-step-input", "zip_code"),
            latitude: getFieldValue(".edit-step-input", "latitude"),
            longitude: getFieldValue(".edit-step-input", "longitude"),
            time_difference:
                Number.isFinite(parsedTimeDifference) && parsedTimeDifference >= 0
                    ? parsedTimeDifference
                    : 0,
            economic_value: normalizeAmount(
                editStepEconomicValueInput?.value ?? "0"
            ),
        };

        if (
            !payload.address ||
            !payload.city ||
            !payload.province ||
            !payload.zip_code ||
            !payload.latitude ||
            !payload.longitude
        ) {
            editStepError.textContent =
                "Conferma l'indirizzo per popolare tutti i campi.";
            return;
        }

        try {
            await axios.put(updateUrl, payload, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            window.location.reload();
        } catch (error) {
            let message =
                "Impossibile aggiornare la tappa. Verifica i campi e riprova.";
            if (
                axios.isAxiosError(error) &&
                error.response?.status === 422
            ) {
                message = "Compila correttamente i campi obbligatori.";
            }
            editStepError.textContent = message;
        }
    };

    const handleStepDelete = async () => {
        if (!selectedStepRow) return;
        const deleteUrl = selectedStepRow.dataset.deleteUrl;
        if (!deleteUrl || !csrfToken) return;

        deleteStepError.textContent = "";

        try {
            await axios.delete(deleteUrl, {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });
            window.location.reload();
        } catch (error) {
            let message = "Impossibile eliminare la tappa. Riprova.";
            deleteStepError.textContent = message;
        }
    };

    if (stepsTableBody) {
        stepsTableBody.addEventListener("click", (event) => {
            const target = event.target.closest(
                ".edit-step-button, .delete-step-button"
            );
            if (!target) return;
            const row = target.closest("tr[data-step-id]");
            if (!row) return;
            if (target.classList.contains("edit-step-button")) {
                openEditModal(row);
            } else if (target.classList.contains("delete-step-button")) {
                openDeleteModal(row);
            }
        });
    }

    if (saveEditStepButton) {
        saveEditStepButton.addEventListener("click", handleStepUpdate);
    }

    if (confirmDeleteStepButton) {
        confirmDeleteStepButton.addEventListener("click", handleStepDelete);
    }

    // Aggiorna costo al cambio veicolo
    if (vehicleSelect && costPerKmInput) {
        vehicleSelect.addEventListener("change", () => {
            const price = vehicleSelect.selectedOptions[0]?.dataset.price;
            if (price !== undefined) {
                const numeric = Number(price);
                costPerKmInput.value = Number.isFinite(numeric)
                    ? numeric.toFixed(4)
                    : "";
            }
        });
    }

    // Mapbox route rendering
    const mapContainer = document.getElementById("daily-travel-map");
    const mapboxToken = mapContainer?.dataset.mapboxToken;
    const mapSteps = mapContainer?.dataset.steps
        ? JSON.parse(mapContainer.dataset.steps)
        : [];

    const showMapMessage = (message) => {
        if (!mapContainer) return;
        mapContainer.innerHTML = `<div class="p-4 text-sm text-gray-500">${message}</div>`;
    };

    const loadMapboxAssets = (accessToken) =>
        new Promise((resolve, reject) => {
            if (window.mapboxgl) {
                window.mapboxgl.accessToken = accessToken;
                resolve();
                return;
            }

            const existing = document.querySelector("script[data-mapbox-gl]");
            if (existing) {
                existing.addEventListener("load", resolve, { once: true });
                existing.addEventListener("error", reject, { once: true });
                return;
            }

            const link = document.createElement("link");
            link.rel = "stylesheet";
            link.href =
                "https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css";
            link.dataset.mapboxGl = "style";
            document.head.appendChild(link);

            const script = document.createElement("script");
            script.src =
                "https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js";
            script.async = true;
            script.defer = true;
            script.dataset.mapboxGl = "loader";
            script.addEventListener(
                "load",
                () => {
                    window.mapboxgl.accessToken = accessToken;
                    resolve();
                },
                { once: true }
            );
            script.addEventListener("error", reject, { once: true });
            document.head.appendChild(script);
        });

    const initRouteMap = async () => {
        if (!mapContainer) return;

        if (!Array.isArray(mapSteps) || mapSteps.length < 2) {
            showMapMessage("Aggiungi almeno due tappe per tracciare il percorso.");
            return;
        }

        if (!mapboxToken) {
            showMapMessage("Token Mapbox mancante.");
            return;
        }

        const validSteps = mapSteps.filter(
            (step) => Number.isFinite(step.lat) && Number.isFinite(step.lng)
        );

        if (validSteps.length < 2) {
            showMapMessage("Coordinate non valide per le tappe.");
            return;
        }

        try {
            await loadMapboxAssets(mapboxToken);
        } catch (error) {
            showMapMessage("Impossibile caricare Mapbox.");
            return;
        }

        mapContainer.innerHTML = "";

        const map = new window.mapboxgl.Map({
            container: mapContainer,
            style: "mapbox://styles/mapbox/streets-v12",
            center: [validSteps[0].lng, validSteps[0].lat],
            zoom: 10,
            attributionControl: false,
        });

        map.addControl(
            new window.mapboxgl.NavigationControl({ showCompass: false }),
            "top-right"
        );

        map.on("load", async () => {
            const bounds = new window.mapboxgl.LngLatBounds();
            validSteps.forEach((step) => {
                const markerEl = document.createElement("div");
                markerEl.textContent = step.step_number
                    ? String(step.step_number)
                    : "";
                markerEl.style.background = "#2563eb";
                markerEl.style.color = "#fff";
                markerEl.style.borderRadius = "9999px";
                markerEl.style.width = "24px";
                markerEl.style.height = "24px";
                markerEl.style.display = "flex";
                markerEl.style.alignItems = "center";
                markerEl.style.justifyContent = "center";
                markerEl.style.fontSize = "12px";
                markerEl.style.fontWeight = "600";
                markerEl.title = step.address ?? "";

                new window.mapboxgl.Marker({ element: markerEl })
                    .setLngLat([step.lng, step.lat])
                    .addTo(map);

                bounds.extend([step.lng, step.lat]);
            });

            map.fitBounds(bounds, { padding: 40, duration: 0 });

            const route = await fetchRoute(validSteps);
            const geometry = route?.geometry ?? {
                type: "LineString",
                coordinates: validSteps.map((step) => [step.lng, step.lat]),
            };

            map.addSource("route", {
                type: "geojson",
                data: {
                    type: "Feature",
                    geometry,
                },
            });

            map.addLayer({
                id: "route-line",
                type: "line",
                source: "route",
                layout: {
                    "line-join": "round",
                    "line-cap": "round",
                },
                paint: {
                    "line-color": "#2563eb",
                    "line-width": 4,
                    "line-opacity": 0.9,
                },
            });
        });
    };

    const fetchRoute = async (steps) => {
        const coordinates = steps
            .map((step) => `${step.lng},${step.lat}`)
            .join(";");
        const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${coordinates}?geometries=geojson&overview=full&access_token=${encodeURIComponent(
            mapboxToken
        )}`;

        try {
            const response = await fetch(url);
            if (!response.ok) return null;
            const data = await response.json();
            return data?.routes?.[0] ?? null;
        } catch (_) {
            return null;
        }
    };

    initRouteMap();
});
