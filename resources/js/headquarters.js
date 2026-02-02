import axios from "axios";

const form = document.querySelector("[data-headquarter-form]");

if (form) {
    const searchInput = document.getElementById(
        "headquarter-address-search-input"
    );
    const validateButton = document.getElementById(
        "validate-headquarter-address-button"
    );
    const errorLabel = document.querySelector(".headquarter-address-error");
    const fieldsSelector = ".headquarter-address-field";
    const searchUrl = form.dataset.searchUrl;

    const mapContainer = document.getElementById("headquarter-map");
    const mapboxToken = mapContainer?.dataset.mapboxToken;
    let map;
    let marker;

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

    const clearError = () => {
        if (errorLabel) {
            errorLabel.textContent = "";
        }
    };

    const showError = (message) => {
        if (errorLabel) {
            errorLabel.textContent = message;
        }
    };

    const clearFields = () => {
        document.querySelectorAll(fieldsSelector).forEach((field) => {
            if (field.tagName === "INPUT") {
                field.value = "";
                field.classList.remove("input-success", "input-warning");
            }
        });
    };

    const setFieldValue = (name, value) => {
        const field = document.querySelector(
            `${fieldsSelector}[name="${name}"]`
        );
        if (!field) return;

        field.value = value ?? "";
        if (value) {
            field.classList.add("input-success");
            field.classList.remove("input-warning");
        } else {
            field.classList.add("input-warning");
            field.classList.remove("input-success");
        }
    };

    const enableFields = () => {
        document.querySelectorAll(fieldsSelector).forEach((field) => {
            field.removeAttribute("disabled");
        });
    };

    const updateMap = async (lat, lng, addressLabel) => {
        if (!mapContainer || !mapboxToken) return;
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const position = [lng, lat];

        if (!map) {
            try {
                await loadMapboxAssets(mapboxToken);
                mapContainer.innerHTML = "";
                map = new window.mapboxgl.Map({
                    container: mapContainer,
                    style: "mapbox://styles/mapbox/streets-v12",
                    center: position,
                    zoom: 14,
                    attributionControl: false,
                });
                map.addControl(
                    new window.mapboxgl.NavigationControl({
                        showCompass: false,
                    }),
                    "top-right"
                );
                marker = new window.mapboxgl.Marker()
                    .setLngLat(position)
                    .addTo(map);
                if (addressLabel) {
                    marker.getElement().setAttribute("title", addressLabel);
                }
            } catch (error) {
                showError("Impossibile caricare Mapbox.");
            }
            return;
        }

        map.setCenter(position);
        marker?.setLngLat(position);
        if (marker && addressLabel) {
            marker.getElement().setAttribute("title", addressLabel);
        }
    };

    const readLatLngFromFields = () => {
        const latField = document.querySelector(
            `${fieldsSelector}[name="latitude"]`
        );
        const lngField = document.querySelector(
            `${fieldsSelector}[name="longitude"]`
        );
        if (!latField || !lngField) return null;
        const lat = parseFloat(latField.value);
        const lng = parseFloat(lngField.value);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
        return { lat, lng };
    };

    const handleSuccessResponse = (data) => {
        const { address_details, latitude, longitude } = data.content ?? {};

        const addressParts = [
            address_details?.road,
            address_details?.house_number,
        ].filter(Boolean);

        setFieldValue("address", addressParts.join(" "));
        setFieldValue(
            "city",
            address_details?.city ??
                address_details?.town ??
                address_details?.village
        );
        setFieldValue("province", address_details?.county);
        setFieldValue("zip_code", address_details?.postcode);
        setFieldValue("latitude", latitude);
        setFieldValue("longitude", longitude);

        enableFields();
        updateMap(
            parseFloat(latitude),
            parseFloat(longitude),
            addressParts.join(" ")
        );
    };

    const handleErrorResponse = (error) => {
        if (error.response) {
            switch (error.response.status) {
                case 404:
                    showError(
                        "Indirizzo non trovato. Controlla l'input e riprova."
                    );
                    return;
                case 500:
                    showError(
                        "Errore del server durante la validazione. Riprova."
                    );
                    return;
                default:
                    showError(
                        "Impossibile recuperare i dati dell'indirizzo. Riprova."
                    );
                    return;
            }
        }

        showError("Errore sconosciuto. Riprova.");
    };

    if (validateButton && searchInput && searchUrl) {
        validateButton.addEventListener("click", () => {
            clearError();
            clearFields();

            if (!searchInput.value.trim()) {
                showError("Inserisci un indirizzo da cercare.");
                return;
            }

            const params = new URLSearchParams({
                address: searchInput.value,
            });

            axios
                .get(`${searchUrl}?${params.toString()}`)
                .then((response) => {
                    handleSuccessResponse(response.data);
                })
                .catch(handleErrorResponse);
        });
    }

    // Render iniziale della mappa (se ci sono coordinate precompilate)
    const initialCoords = readLatLngFromFields();
    if (initialCoords) {
        updateMap(
            initialCoords.lat,
            initialCoords.lng,
            mapContainer?.dataset.address
        );
    }
}
