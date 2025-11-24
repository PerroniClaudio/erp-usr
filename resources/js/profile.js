import axios from "axios";

const pageRoot = document.getElementById("profile-page");

if (pageRoot) {
    const searchUrl = pageRoot.dataset.searchUrl;

    const forms = {
        residence: {
            prefix: "",
            searchInput: document.getElementById("profile-residence-search"),
            errorLabel: document.getElementById("residence-search-error"),
        },
        location: {
            prefix: "location_",
            searchInput: document.getElementById("profile-location-search"),
            errorLabel: document.getElementById("location-search-error"),
        },
    };

    const pickCity = (details) =>
        details.city ?? details.town ?? details.village ?? "";

    const setFieldValue = (name, value) => {
        const field = pageRoot.querySelector(`input[name="${name}"]`);
        if (field) {
            field.value = value ?? "";
        }
    };

    const clearError = (formKey) => {
        const { errorLabel } = forms[formKey];
        if (errorLabel) {
            errorLabel.textContent = "";
        }
    };

    const setError = (formKey, message) => {
        const { errorLabel } = forms[formKey];
        if (errorLabel) {
            errorLabel.textContent = message;
        }
    };

    const populateAddress = (formKey, addressDetails, latitude, longitude) => {
        const { prefix } = forms[formKey];

        setFieldValue(`${prefix}address`, addressDetails.road ?? "");
        setFieldValue(`${prefix}street_number`, addressDetails.house_number ?? "");
        setFieldValue(`${prefix}city`, pickCity(addressDetails));
        setFieldValue(`${prefix}province`, addressDetails.county ?? "");
        setFieldValue(`${prefix}postal_code`, addressDetails.postcode ?? "");
        setFieldValue(`${prefix}latitude`, latitude);
        setFieldValue(`${prefix}longitude`, longitude);
    };

    const runSearch = (formKey) => {
        const { searchInput } = forms[formKey];
        if (!searchInput || !searchUrl) {
            return;
        }

        const query = searchInput.value.trim();
        if (!query.length) {
            setError(formKey, "Inserisci un indirizzo da validare.");
            return;
        }

        axios
            .get(`${searchUrl}?address=${encodeURIComponent(query)}`)
            .then((response) => {
                clearError(formKey);
                const { address_details, latitude, longitude } = response.data.content;
                populateAddress(formKey, address_details, latitude, longitude);
            })
            .catch((error) => {
                let message = "Impossibile recuperare l'indirizzo. Riprova.";
                if (error.response?.status === 404) {
                    message = "Indirizzo non trovato. Controlla e riprova.";
                }
                setError(formKey, message);
            });
    };

    document.querySelectorAll("[data-search]").forEach((button) => {
        const key = button.getAttribute("data-search");
        if (!forms[key]) {
            return;
        }

        button.addEventListener("click", () => runSearch(key));
    });
}
