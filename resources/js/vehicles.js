const searchVehicle = document.querySelector("#search-vehicle-button");

function handleSearchVehicle() {
    let query = document.querySelector("#search-vehicle").value;

    axios
        .get(`/vehicles/search?query=${query}`)
        .then(function (response) {
            let models = response.data.models;
            let modelSelector = document.querySelector("#model");
            modelSelector.innerHTML = "";

            let defaultOption = document.createElement("option");
            defaultOption.textContent = "Seleziona modello";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            modelSelector.appendChild(defaultOption);

            Object.values(models).forEach(function (model) {
                let option = document.createElement("option");
                option.value = model.id;
                option.textContent = model.model;
                modelSelector.appendChild(option);
            });

            modelSelector.disabled = false;

            const vehicleSelectorContainer = document.querySelector(
                "#vehicle-selector-container"
            );
            vehicleSelectorContainer.classList.remove("hidden");
        })
        .catch(function (error) {
            console.error("Error fetching models:", error);
        });
}

searchVehicle.addEventListener("click", handleSearchVehicle);

document
    .querySelector("#search-vehicle")
    .addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            handleSearchVehicle();
        }
    });

const modelSelector = document.querySelector("#model");

modelSelector.addEventListener("change", function () {
    let hiddenModelField = document.querySelector("#vehicle_id");
    hiddenModelField.value = modelSelector.value;
});
