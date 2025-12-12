document.addEventListener("DOMContentLoaded", () => {
    const preview = document.querySelector("[data-structure-preview]");
    if (!preview) return;

    const metaContainer = preview.querySelector("[data-preview-meta]");
    const stepsTable = document.querySelector("[data-steps-table]");
    const companySelect = document.getElementById("company_id");
    const structures = JSON.parse(preview.dataset.structures || "{}");
    const mapContainer = document.getElementById("daily-travel-map");
    const googleApiKey = preview.dataset.googleApiKey;
    const distanceSummary = document.querySelector("[data-distance-summary]");
    const startLocationValue = preview.dataset.startLocationValue || "office";
    const headquartersMap = JSON.parse(preview.dataset.headquarters || "{}");
    const userHeadquarter = safeJsonParse(preview.dataset.userHeadquarter);
    const intermediateSelect = document.getElementById(
        "intermediate_headquarter_id"
    );
    const addIntermediateButton = document.getElementById(
        "add_intermediate_button"
    );
    const intermediateList = document.querySelector("[data-intermediate-list]");
    let intermediateSteps = [];

    const labels = {
        missing: preview.dataset.missingMessage || "",
        vehicle: preview.dataset.vehicleLabel || "",
        vehicleNone: preview.dataset.vehicleNone || "",
        costPerKm: preview.dataset.costPerKmLabel || "",
        economicValue: preview.dataset.economicValueLabel || "",
        startLocation: preview.dataset.startLocationLabel || "",
        startLocationOffice: preview.dataset.startLocationOfficeLabel || "",
        stepsTitle: preview.dataset.stepsTitle || "",
        stepsEmpty: preview.dataset.stepsEmpty || "",
        stepLabel: preview.dataset.stepLabel || "",
        distanceTitle: preview.dataset.distanceTitle || "",
        distancePath: preview.dataset.distancePath || "",
        distanceDistance: preview.dataset.distanceDistance || "",
        distanceEmpty: preview.dataset.distanceEmpty || "",
        mapPlaceholder: preview.dataset.mapPlaceholder || "",
        currency: preview.dataset.currencySymbol || "€",
        routeTitle: preview.dataset.routeTitle || "",
        routeStart: preview.dataset.routeStartLabel || "",
        routeEmpty: preview.dataset.routeEmpty || "",
        routeNone: preview.dataset.routeNone || "",
        routeMissingHeadquarter: preview.dataset.routeMissingHeadquarter || "",
    };

    const formatCurrency = (value, decimals = 2) => {
        const numeric = Number(value ?? 0);
        const safeDecimals = Number.isInteger(decimals) ? decimals : 2;
        return `${labels.currency} ${
            Number.isFinite(numeric)
                ? numeric.toFixed(safeDecimals)
                : Number(0).toFixed(safeDecimals)
        }`;
    };

    const resolveStartLocationLabel = () => labels.startLocationOffice;

    const getSelectedCompanyId = () =>
        companySelect?.value || preview.dataset.selectedCompany;

    const getSelectedStartLocation = () => startLocationValue;

    const renderStructure = (structure, startLocation) => {
        if (!structure) {
            if (metaContainer) {
                metaContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.missing}</p>`;
            }
            if (stepsTable) {
                stepsTable.innerHTML = `<tr><td colspan="5" class="text-center text-sm text-base-content/70">${labels.stepsEmpty}</td></tr>`;
            }
            if (mapContainer) {
                mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.mapPlaceholder}</p>`;
            }
            if (distanceSummary) {
                distanceSummary.innerHTML = `<p class="text-sm text-base-content/70">${labels.distanceEmpty}</p>`;
            }
            return;
        }

        const steps = Array.isArray(structure.steps) ? structure.steps : [];
        const structureStartLocation = structure.start_location || startLocation;
        const locationLabel = resolveStartLocationLabel(structureStartLocation);
        const stepsRows = steps.length
            ? steps
                  .map(
                      (step) => `
                                <tr>
                                    <td class="w-12">${step.step_number}</td>
                                    <td>${step.address}</td>
                                    <td>${step.city}</td>
                                    <td>${step.province}</td>
                                    <td>${step.zip_code}</td>
                                </tr>
                            `
                  )
                  .join("")
            : `<tr><td colspan="5" class="text-center text-sm text-base-content/70">${labels.stepsEmpty}</td></tr>`;

        if (metaContainer) {
            metaContainer.innerHTML = `
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs uppercase text-base-content/60">${labels.vehicle}</p>
                        <p class="font-semibold">${
                            structure.vehicle?.label ?? labels.vehicleNone
                        }</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">${labels.costPerKm}</p>
                        <p class="font-semibold">${formatCurrency(
                            structure.cost_per_km,
                            4
                        )}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">${labels.startLocation}</p>
                        <p class="font-semibold">${locationLabel}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">${labels.economicValue}</p>
                        <p class="font-semibold">${formatCurrency(
                            structure.economic_value
                        )}</p>
                    </div>
                </div>
            `;
        }

        if (stepsTable) {
            stepsTable.innerHTML = stepsRows;
        }

        renderDistances(steps);
        renderMap(structure);
    };

    const renderCurrentSelection = () => {
        const companyId = getSelectedCompanyId();
        const selectedStartLocation = getSelectedStartLocation();
        const structureGroup = companyId ? structures[companyId] : null;
        const structure = structureGroup?.[selectedStartLocation] ?? null;
        renderStructure(structure, selectedStartLocation);
    };

    const populateIntermediateSelect = () => {
        if (!intermediateSelect) return;
        const companyId = getSelectedCompanyId();
        const list = Array.isArray(headquartersMap[companyId])
            ? headquartersMap[companyId]
            : [];
        const options = [
            `<option value="">${labels.routeNone}</option>`,
            ...list.map(
                (hq) =>
                    `<option value="${hq.id}">${hq.name} - ${hq.city}</option>`
            ),
        ];
        intermediateSelect.innerHTML = options.join("");
    };

    const renderIntermediates = () => {
        if (!intermediateList) return;

        if (!userHeadquarter) {
            intermediateList.innerHTML = `<p class="text-sm text-error">${labels.routeMissingHeadquarter}</p>`;
            return;
        }

        const steps = [
            userHeadquarter,
            ...intermediateSteps,
            userHeadquarter,
        ];

        const rows = steps
            .map((step, index) => {
                const isEndpoint =
                    index === 0 || index === steps.length - 1;
                const label = isEndpoint
                    ? labels.routeStart
                    : labels.stepLabel.replace(":number", index);

                const removeButton =
                    !isEndpoint && intermediateSteps.length
                        ? `<button type="button" class="btn btn-xs btn-ghost" data-remove-step="${step.id}">✕</button>`
                        : "";

                return `
                    <div class="flex items-center justify-between p-3 bg-base-100 border border-base-200 rounded-lg mb-2">
                        <div>
                            <p class="text-xs uppercase text-base-content/60">${label}</p>
                            <p class="font-semibold">${step.name ?? step.address ?? ""}</p>
                            <p class="text-sm text-base-content/70">${[step.address, step.city].filter(Boolean).join(" - ")}</p>
                        </div>
                        ${removeButton}
                    </div>
                `;
            })
            .join("");

        const hiddenInputs = intermediateSteps
            .map(
                (step) =>
                    `<input type="hidden" name="intermediate_headquarter_ids[]" value="${step.id}">`
            )
            .join("");

        intermediateList.innerHTML =
            rows || `<p class="text-sm text-base-content/70">${labels.routeEmpty}</p>`;

        intermediateList
            .querySelectorAll("[data-remove-step]")
            .forEach((btn) => {
                btn.addEventListener("click", () => {
                    const targetId = Number(btn.dataset.removeStep);
                    intermediateSteps = intermediateSteps.filter(
                        (item) => Number(item.id) !== targetId
                    );
                    renderIntermediates();
                });
            });

        if (hiddenInputs) {
            const container = document.createElement("div");
            container.innerHTML = hiddenInputs;
            intermediateList.appendChild(container);
        }
    };

    const resetIntermediatesForCompany = () => {
        intermediateSteps = [];
        populateIntermediateSelect();
        renderIntermediates();
    };

    const addIntermediate = () => {
        if (!intermediateSelect) return;
        const value = Number(intermediateSelect.value);
        if (!value) return;
        const companyId = getSelectedCompanyId();
        const list = Array.isArray(headquartersMap[companyId])
            ? headquartersMap[companyId]
            : [];
        const found = list.find((hq) => Number(hq.id) === value);
        if (!found) return;
        const already = intermediateSteps.some(
            (item) => Number(item.id) === value
        );
        if (already) return;
        intermediateSteps.push(found);
        renderIntermediates();
    };

    companySelect?.addEventListener("change", () => {
        renderCurrentSelection();
        resetIntermediatesForCompany();
    });

    addIntermediateButton?.addEventListener("click", addIntermediate);

    resetIntermediatesForCompany();
    renderCurrentSelection();

    function renderDistances(steps) {
        if (!distanceSummary) return;
        if (!Array.isArray(steps) || steps.length < 2) {
            distanceSummary.innerHTML = `<p class="text-sm text-base-content/70">${labels.distanceEmpty}</p>`;
            return;
        }

        const distances = calculateDistances(steps);
        if (!distances.length) {
            distanceSummary.innerHTML = `<p class="text-sm text-base-content/70">${labels.distanceEmpty}</p>`;
            return;
        }

        distanceSummary.innerHTML = distances
            .map(
                ({ from, to, distance }) => `
                    <div class="p-3 rounded-lg bg-base-100 border border-base-200">
                        <div class="text-xs uppercase text-gray-500 mb-1">
                            ${labels.distancePath}
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="badge badge-outline">
                                ${from.city} - ${from.address}
                            </div>
                            <span class="text-sm text-gray-500">→</span>
                            <div class="badge badge-outline">
                                ${to.city} - ${to.address}
                            </div>
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="font-medium">${labels.distanceDistance}:</span>
                            ${distance.toFixed(2)} km
                        </div>
                    </div>
                `
            )
            .join("");
    }

    function calculateDistances(steps) {
        const pairs = [];
        for (let i = 0; i < steps.length - 1; i++) {
            const from = steps[i];
            const to = steps[i + 1];

            if (
                Number.isFinite(from.latitude) &&
                Number.isFinite(from.longitude) &&
                Number.isFinite(to.latitude) &&
                Number.isFinite(to.longitude)
            ) {
                pairs.push({
                    from,
                    to,
                    distance: haversineKm(
                        from.latitude,
                        from.longitude,
                        to.latitude,
                        to.longitude
                    ),
                });
            }
        }
        return pairs;
    }

    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const toRad = (deg) => (deg * Math.PI) / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(toRad(lat1)) *
                Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) ** 2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function renderMap(structure) {
        if (!mapContainer || !googleApiKey) return;

        const steps = (structure?.steps || []).filter(
            (step) =>
                Number.isFinite(step.latitude) && Number.isFinite(step.longitude)
        );

        if (steps.length < 2) {
            mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.mapPlaceholder}</p>`;
            return;
        }

        loadGoogleMapsScript(googleApiKey)
            .then(() => drawRoute(mapContainer, steps))
            .catch(() => {
                mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.missing}</p>`;
            });
    }

    function loadGoogleMapsScript(apiKey) {
        return new Promise((resolve, reject) => {
            if (window.google?.maps) {
                resolve();
                return;
            }
            const existing = document.querySelector("script[data-google-maps]");
            if (existing) {
                existing.addEventListener("load", resolve, { once: true });
                existing.addEventListener("error", reject, { once: true });
                return;
            }
            const script = document.createElement("script");
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
            script.async = true;
            script.defer = true;
            script.dataset.googleMaps = "loader";
            script.addEventListener("load", resolve, { once: true });
            script.addEventListener("error", reject, { once: true });
            document.head.appendChild(script);
        });
    }

    function drawRoute(container, steps) {
        container.innerHTML = "";
        const map = new google.maps.Map(container, {
            center: { lat: steps[0].latitude, lng: steps[0].longitude },
            zoom: 10,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
        });

        const validSteps = steps.map((step) => ({
            lat: step.latitude,
            lng: step.longitude,
            address: step.address,
            step_number: step.step_number,
        }));

        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: "#2563eb",
                strokeOpacity: 0.9,
                strokeWeight: 5,
            },
        });

        const waypoints = validSteps.slice(1, -1).map((step) => ({
            location: { lat: step.lat, lng: step.lng },
            stopover: true,
        }));

        directionsService.route(
            {
                origin: {
                    lat: validSteps[0].lat,
                    lng: validSteps[0].lng,
                },
                destination: {
                    lat: validSteps[validSteps.length - 1].lat,
                    lng: validSteps[validSteps.length - 1].lng,
                },
                waypoints,
                travelMode: google.maps.TravelMode.DRIVING,
                optimizeWaypoints: false,
            },
            (response, status) => {
                if (
                    status === "OK" ||
                    status === google.maps.DirectionsStatus.OK
                ) {
                    directionsRenderer.setDirections(response);
                    renderMarkers(map, validSteps);
                } else {
                    renderMarkersAndPolyline(map, validSteps);
                }
            }
        );
    }

    function renderMarkers(map, steps) {
        const bounds = new google.maps.LatLngBounds();
        steps.forEach((step, index) => {
            const position = { lat: step.lat, lng: step.lng };
            bounds.extend(position);
            new google.maps.Marker({
                position,
                map,
                label: step.step_number
                    ? String(step.step_number)
                    : String(index + 1),
                title: step.address ?? "",
            });
        });
        map.fitBounds(bounds);
    }

    function renderPolyline(map, steps) {
        return new google.maps.Polyline({
            path: steps.map((step) => ({ lat: step.lat, lng: step.lng })),
            geodesic: true,
            strokeColor: "#2563eb",
            strokeOpacity: 0.9,
            strokeWeight: 4,
            map,
        });
    }

    function renderMarkersAndPolyline(map, steps) {
        renderMarkers(map, steps);
        renderPolyline(map, steps);
    }

    function safeJsonParse(value) {
        try {
            return value ? JSON.parse(value) : null;
        } catch (_) {
            return null;
        }
    }
});
