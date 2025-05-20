import axios from "axios";

const user_id = document.querySelector("#user_id").value;

// Configura la ricerca dell'indirizzo associando input, pulsante e classe del form
const setupAddressSearch = (inputId, buttonId, formClass) => {
    const addressInput = document.getElementById(inputId);
    const addressSearch = document.getElementById(buttonId);

    // Rimuove tutti i messaggi di errore visualizzati
    const clearErrorMessages = () => {
        document
            .querySelectorAll(".text-error.label")
            .forEach((errorElement) => {
                errorElement.innerHTML = "";
            });
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
            address_details.town || address_details.city
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
        const errorLabel = document.querySelector(".text-error.label");
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
};

// Configura la ricerca per il form di residenza
setupAddressSearch(
    "address-search-input",
    "validate-address-button",
    "residence-form"
);

// Configura la ricerca per il form del recapito
setupAddressSearch(
    "location-address-search-input",
    "validate-location-address-button",
    "location-form"
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
