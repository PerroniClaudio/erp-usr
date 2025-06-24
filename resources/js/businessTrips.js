import axios from "axios";

const validateButton = document.getElementById("validate-address");
const is_edit = document.querySelector("#is_edit").value == 1;

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

            document.querySelector("#error-message").classList.add("hidden");
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
    let { address_details, latitude, longitude } = content;
    address_details.house_number =
        address_details.house_number !== undefined
            ? address_details.house_number
            : "";

    document.querySelector(
        "#address"
    ).value = `${address_details.road} ${address_details.house_number}`;
    document.querySelector("#city").value =
        address_details.city !== undefined
            ? address_details.city
            : address_details.town !== undefined
            ? address_details.town
            : address_details.village !== undefined
            ? address_details.village
            : "";
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

if (is_edit) {
    const fileInput = document.querySelector("#file-input");
    const uploadStart = document.querySelector("#upload-start");
    const fileUploadSuccess = document.querySelector("#upload-success");
    const submitButton = document.querySelector("#submit-file-button");
    const cancelUploadButton = document.querySelector("#cancel-upload-button");
    const filenameDisplay = document.querySelector("#filename-display");
    const filenameDisplayContainer = document.querySelector("#upload-info");

    const handleUploadStartClick = () => fileInput.click();
    const handleFileUploadSuccessClick = () => submitButton.click();
    const handleCancelUploadClick = () => {
        fileInput.value = "";
        fileUploadSuccess.classList.add("hidden");
        uploadStart.classList.remove("hidden");
        filenameDisplay.textContent = "Nessun file selezionato";
        filenameDisplayContainer.classList.add("hidden");
    };
    const handleFileInputChange = (event) => {
        const file = event.target.files[0];
        const hasFile = !!file;
        fileUploadSuccess.classList.toggle("hidden", !hasFile);
        uploadStart.classList.toggle("hidden", hasFile);
        filenameDisplay.textContent = hasFile
            ? file.name
            : "Nessun file selezionato";
        filenameDisplayContainer.classList.toggle("hidden", !hasFile);
    };

    if (uploadStart) {
        uploadStart.addEventListener("click", handleUploadStartClick);
    }
    if (fileUploadSuccess) {
        fileUploadSuccess.addEventListener(
            "click",
            handleFileUploadSuccessClick
        );
    }
    if (fileInput) {
        fileInput.addEventListener("change", handleFileInputChange);
    }
    if (cancelUploadButton) {
        cancelUploadButton.addEventListener("click", handleCancelUploadClick);
    }
}
