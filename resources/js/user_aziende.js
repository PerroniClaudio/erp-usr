const modalOpener = document.querySelector("#associate-users-modal-opener");
const template = document.querySelector("#company-list-item-template");
const associatedTemplate = document.querySelector(
    "#company-added-list-item-template"
);
const noCompaniesTemplate = document.querySelector(
    "#company-list-none-available-template"
);

let availableCompanies = [];
let associatedCompanies = [];

modalOpener.addEventListener("click", handleModalOpen);

function handleModalOpen() {
    const userId = document.querySelector("#user_id").value;

    axios
        .get(`/admin/personnel/companies/users/available/${userId}`)
        .then((response) => {
            availableCompanies = response.data.companies;
            populateCompaniesTable();
        })
        .catch((error) => console.error("Error fetching companies:", error));
}

function populateCompaniesTable() {
    const tableBody = document.querySelector("#associate-users-table-body");
    tableBody.innerHTML = "";

    if (availableCompanies.length === 0) {
        displayNoCompaniesMessage(tableBody);
        return;
    }

    availableCompanies.forEach((company) => addCompanyRow(tableBody, company));
    associate_users_modal.showModal();
}

function displayNoCompaniesMessage(tableBody) {
    const clone = noCompaniesTemplate.content.cloneNode(true);
    tableBody.appendChild(clone);
    associate_users_modal.showModal();
}

function addCompanyRow(tableBody, company) {
    const clone = template.content.cloneNode(true);

    const companyNameField = clone.querySelector(".company-name-field");
    companyNameField.innerText = company.name;

    const addButton = clone.querySelector(".add-company-button");
    addButton.setAttribute("data-company-id", company.id);
    addButton.addEventListener("click", () => handleAddCompany(company));

    tableBody.appendChild(clone);
}

function handleAddCompany(company) {
    // Move the company from availableCompanies to associatedCompanies
    availableCompanies = availableCompanies.filter(
        (availableCompany) => availableCompany.id !== company.id
    );
    associatedCompanies.push(company);

    // Update the tables
    populateCompaniesTable();
    updateAssociatedCompaniesTable();
}

function updateAssociatedCompaniesTable() {
    const tableBody = document.querySelector(
        "#associated-companies-table-body"
    );
    tableBody.innerHTML = "";

    associatedCompanies.forEach((company) => {
        const clone = associatedTemplate.content.cloneNode(true);

        const companyNameField = clone.querySelector(".company-name-field");
        companyNameField.innerText = company.name;

        const removeButton = clone.querySelector(".remove-company-button");
        removeButton.setAttribute("data-company-id", company.id);
        removeButton.addEventListener("click", () =>
            handleRemoveCompany(company)
        );

        tableBody.appendChild(clone);
    });
}

function handleRemoveCompany(company) {
    // Move the company from associatedCompanies to availableCompanies
    associatedCompanies = associatedCompanies.filter(
        (associatedCompany) => associatedCompany.id !== company.id
    );
    availableCompanies.push(company);

    // Update the tables
    populateCompaniesTable();
    updateAssociatedCompaniesTable();
}

// Salva le aziende associate

const saveButton = document.querySelector("#save-companies-button");

saveButton.addEventListener("click", async () => {
    let user_id = document.querySelector("#user_id").value;

    let formData = new FormData();
    const companiesToAssociate = associatedCompanies.map(
        (company) => company.id
    );
    formData.append("companies", JSON.stringify(companiesToAssociate));

    try {
        const response = await axios.post(
            `/admin/personnel/companies/users/${user_id}/associate-companies`,
            formData
        );

        if (response.status === 200) {
            window.location.reload();
        }
    } catch (error) {
        console.error("Error associating companies:", error);
    }
});
