document.addEventListener("DOMContentLoaded", () => {
    const preview = document.querySelector("[data-structure-preview]");
    if (!preview) return;

    const metaContainer = preview.querySelector("[data-preview-meta]");
    const stepsTable = document.querySelector("[data-steps-table]");
    const mapContainer = document.getElementById("daily-travel-map");
    const mapboxToken = preview.dataset.mapboxToken;
    const distanceSummary = document.querySelector("[data-distance-summary]");
    const startLocationValue = preview.dataset.startLocationValue || "office";
    const headquartersMap = JSON.parse(preview.dataset.headquarters || "{}");
    const userHeadquarter = safeJsonParse(preview.dataset.userHeadquarter);
    const structures = JSON.parse(preview.dataset.structures || "{}");
    const selectedCompanyId = preview.dataset.selectedCompany || "";

    const intermediateList = document.querySelector("[data-intermediate-list]");
    const openModalButton = document.getElementById("open_intermediate_modal");
    const intermediateModal = document.getElementById("intermediate_modal");
    const companySelect = document.getElementById("intermediate_company_id");
    const intermediateSelect = document.getElementById(
        "intermediate_headquarter_id"
    );
    const addIntermediateButton = document.getElementById(
        "add_intermediate_button"
    );

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

    const renderStructureMeta = () => {
        const structureGroup = selectedCompanyId
            ? structures[selectedCompanyId]
            : null;
        const structure = structureGroup?.[startLocationValue] ?? null;

        if (!structure) {
            if (metaContainer) {
                metaContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.missing}</p>`;
            }
            return;
        }

        const locationLabel = resolveStartLocationLabel();
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
    };

    const getCompanyHeadquarters = (companyId) =>
        Array.isArray(headquartersMap[companyId])
            ? headquartersMap[companyId]
            : [];

    const populateIntermediateSelect = () => {
        if (!intermediateSelect) return;
        const companyId = companySelect?.value || "";
        const list = getCompanyHeadquarters(companyId);
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

                const companyName = step.company_name
                    ? `<p class="text-xs uppercase text-base-content/60">${step.company_name}</p>`
                    : "";

                return `
                    <div class="flex items-center justify-between p-3 bg-base-100 border border-base-200 rounded-lg mb-2">
                        <div>
                            <p class="text-xs uppercase text-base-content/60">${label}</p>
                            <p class="font-semibold">${step.name ?? step.address ?? ""}</p>
                            <p class="text-sm text-base-content/70">${[step.address, step.city].filter(Boolean).join(" - ")}</p>
                            ${companyName}
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
                    renderRoutePreview();
                });
            });

        if (hiddenInputs) {
            const container = document.createElement("div");
            container.innerHTML = hiddenInputs;
            intermediateList.appendChild(container);
        }
    };

    const normalizeStep = (step, index) => {
        const latitude = Number(step.latitude);
        const longitude = Number(step.longitude);
        return {
            ...step,
            step_number: index + 1,
            latitude: Number.isFinite(latitude) ? latitude : step.latitude,
            longitude: Number.isFinite(longitude) ? longitude : step.longitude,
        };
    };

    const buildRouteSteps = () => {
        if (!userHeadquarter) return [];
        return [userHeadquarter, ...intermediateSteps, userHeadquarter].map(
            (step, index) => normalizeStep(step, index)
        );
    };

    const renderRoutePreview = () => {
        const steps = buildRouteSteps();

        if (stepsTable) {
            if (!steps.length) {
                stepsTable.innerHTML = `<tr><td colspan="5" class="text-center text-sm text-base-content/70">${labels.stepsEmpty}</td></tr>`;
            } else {
                stepsTable.innerHTML = steps
                    .map(
                        (step) => `
                            <tr>
                                <td class="w-12">${step.step_number}</td>
                                <td>${step.address ?? ""}</td>
                                <td>${step.city ?? ""}</td>
                                <td>${step.province ?? ""}</td>
                                <td>${step.zip_code ?? ""}</td>
                            </tr>
                        `
                    )
                    .join("");
            }
        }

        renderDistances(steps);
        renderMap(steps);
    };

    const addIntermediate = () => {
        if (!intermediateSelect || !companySelect) return;
        const value = Number(intermediateSelect.value);
        if (!value) return;
        const companyId = companySelect.value;
        const list = getCompanyHeadquarters(companyId);
        const found = list.find((hq) => Number(hq.id) === value);
        if (!found) return;
        const already = intermediateSteps.some(
            (item) => Number(item.id) === value
        );
        if (already) return;
        intermediateSteps.push(found);
        renderIntermediates();
        renderRoutePreview();
        if (intermediateModal) {
            intermediateModal.close();
        }
    };

    const setDefaultCompany = () => {
        if (!companySelect) return;
        if (companySelect.value) return;
        const firstOption = Array.from(companySelect.options).find(
            (option) => option.value
        );
        if (firstOption) {
            companySelect.value = firstOption.value;
        }
    };

    openModalButton?.addEventListener("click", () => {
        if (intermediateModal?.showModal) {
            setDefaultCompany();
            populateIntermediateSelect();
            intermediateModal.showModal();
        }
    });

    companySelect?.addEventListener("change", () => {
        populateIntermediateSelect();
    });

    addIntermediateButton?.addEventListener("click", addIntermediate);

    renderStructureMeta();
    renderIntermediates();
    renderRoutePreview();

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

    let mapInstance = null;

    function renderMap(steps) {
        if (!mapContainer) return;
        if (!mapboxToken) {
            mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.missing}</p>`;
            return;
        }

        const validSteps = (steps || []).filter(
            (step) =>
                Number.isFinite(step.latitude) &&
                Number.isFinite(step.longitude)
        );

        if (validSteps.length < 2) {
            mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.mapPlaceholder}</p>`;
            return;
        }

        loadMapboxAssets(mapboxToken)
            .then(() => drawRoute(mapContainer, validSteps))
            .catch(() => {
                mapContainer.innerHTML = `<p class="text-sm text-base-content/70">${labels.missing}</p>`;
            });
    }

    function loadMapboxAssets(accessToken) {
        return new Promise((resolve, reject) => {
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
            script.addEventListener("load", () => {
                window.mapboxgl.accessToken = accessToken;

                const langScript = document.createElement("script");
                langScript.src =
                    "https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-language/v1.0.0/mapbox-gl-language.js";
                langScript.async = true;
                langScript.defer = true;
                langScript.dataset.mapboxGlLanguage = "loader";
                langScript.addEventListener("load", resolve, { once: true });
                langScript.addEventListener("error", resolve, { once: true });
                document.head.appendChild(langScript);
            }, { once: true });
            script.addEventListener("error", reject, { once: true });
            document.head.appendChild(script);
        });
    }

    async function drawRoute(container, steps) {
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }

        container.innerHTML = "";

        const validSteps = steps.map((step, index) => ({
            lat: step.latitude,
            lng: step.longitude,
            address: step.address,
            step_number: step.step_number ?? index + 1,
        }));

        mapInstance = new window.mapboxgl.Map({
            container,
            style: "mapbox://styles/mapbox/streets-v12",
            center: [validSteps[0].lng, validSteps[0].lat],
            zoom: 10,
            attributionControl: false,
        });

        mapInstance.addControl(
            new window.mapboxgl.NavigationControl({ showCompass: false }),
            "top-right"
        );
        if (window.MapboxLanguage) {
            mapInstance.addControl(
                new window.MapboxLanguage({ defaultLanguage: "it" })
            );
        }

        mapInstance.on("load", async () => {
            const bounds = new window.mapboxgl.LngLatBounds();
            validSteps.forEach((step, index) => {
                const markerEl = document.createElement("div");
                markerEl.textContent = step.step_number
                    ? String(step.step_number)
                    : String(index + 1);
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
                    .addTo(mapInstance);

                bounds.extend([step.lng, step.lat]);
            });

            mapInstance.fitBounds(bounds, { padding: 40, duration: 0 });

            const route = await fetchRoute(validSteps);
            const geometry = route?.geometry ?? {
                type: "LineString",
                coordinates: validSteps.map((step) => [step.lng, step.lat]),
            };

            mapInstance.addSource("route", {
                type: "geojson",
                data: {
                    type: "Feature",
                    geometry,
                },
            });

            mapInstance.addLayer({
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
    }

    async function fetchRoute(steps) {
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
    }

    function safeJsonParse(value) {
        try {
            return value ? JSON.parse(value) : null;
        } catch (_) {
            return null;
        }
    }
});
