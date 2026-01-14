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
    const mapApiKey = mapContainer?.dataset.apiKey;
    let map;
    let marker;

    const loadGoogleMapsScript = (apiKey) =>
        new Promise((resolve, reject) => {
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
        if (!mapContainer || !mapApiKey) return;
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

        const position = { lat, lng };

        if (!map) {
            try {
                await loadGoogleMapsScript(mapApiKey);
                map = new google.maps.Map(mapContainer, {
                    center: position,
                    zoom: 14,
                });
                marker = new google.maps.Marker({
                    position,
                    map,
                    title: addressLabel ?? "",
                });
            } catch (error) {
                showError("Impossibile caricare la mappa Google.");
            }
            return;
        }

        map.setCenter(position);
        marker?.setPosition(position);
        if (marker && addressLabel) {
            marker.setTitle(addressLabel);
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
