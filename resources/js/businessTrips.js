import axios from "axios";

const validateButton = document.getElementById("validate-address");

validateButton.addEventListener("click", async () => {
    const formData = {
        address: document.querySelector("#address").value,
        city: document.querySelector("#city").value,
        province: document.querySelector("#province").value,
        zip_code: document.querySelector("#zip_code").value,
    };

    try {
        const response = await axios.get(
            `/standard/business-trips/validate-address`,
            { params: formData }
        );

        clearErrorMessages();

        if (response.data.status === "success") {
            populateAddressFields(response.data.content);
            toggleIcons(true);
            showSubmitButton();
            removeAlertInfo();
        } else {
            displayErrorMessage(response.data.message);
            toggleIcons(false);
        }
    } catch (error) {
        handleError(error);
        toggleIcons(false);
    }
});

function clearErrorMessages() {
    document.querySelectorAll(".text-error.label").forEach((errorElement) => {
        errorElement.innerHTML = "";
    });
}

function populateAddressFields(content) {
    const { address_details, latitude, longitude } = content;

    document.querySelector(
        "#address"
    ).value = `${address_details.road} ${address_details.house_number}`;
    document.querySelector("#city").value = address_details.town;
    document.querySelector("#province").value = address_details.county;
    document.querySelector("#zip_code").value = address_details.postcode;
    document.querySelector("#latitude").value = latitude;
    document.querySelector("#longitude").value = longitude;
}

function toggleIcons(isValid) {
    const errorIcon = document.querySelector("#address-invalid-icon");
    const checkIcon = document.querySelector("#address-valid-icon");

    if (isValid) {
        errorIcon.classList.add("hidden");
        checkIcon.classList.remove("hidden");
    } else {
        errorIcon.classList.remove("hidden");
        checkIcon.classList.add("hidden");
    }
}

function showSubmitButton() {
    document
        .querySelectorAll(".submit-button-container")
        .forEach((container) => container.classList.remove("hidden"));
}

function removeAlertInfo() {
    document.querySelectorAll(".alert-info").forEach((container) => {
        container.remove();
    });
}

function displayErrorMessage(message) {
    const errorElement = document.querySelector("#error-message");
    errorElement.innerHTML = message;
}

function handleError(error) {
    if (error.response) {
        if (error.response.status === 422) {
            displayValidationErrors(error.response.data.errors);
        } else if (error.response.status === 404) {
            displayErrorMessage(error.response.data.message);
        }
    } else {
        console.error("An unexpected error occurred:", error);
    }
}

function displayValidationErrors(errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const errorElement = document.querySelector(`#${field}-error`);
        if (errorElement) {
            errorElement.innerHTML = messages.join(", ");
        }
    }
}
