const groupModalOpener = document.querySelector(
    "#associate-groups-users-modal-opener"
);

const template = document.querySelector("#group-list-item-template");
const associatedTemplate = document.querySelector(
    "#group-added-list-item-template"
);
const noGroupsTemplate = document.querySelector(
    "#group-list-none-available-template"
);

let availableGroups = [];
let associatedGroups = [];

groupModalOpener.addEventListener("click", handleGroupModalOpen);

function handleGroupModalOpen() {
    const userId = document.querySelector("#user_id").value;

    axios
        .get(`/admin/personnel/groups/users/available/${userId}`)
        .then((response) => {
            availableGroups = response.data.groups;
            populateGroupsTable();
        })
        .catch((error) => console.error("Error fetching groups:", error));
}

function populateGroupsTable() {
    const tableBody = document.querySelector(
        "#associate-groups-users-table-body"
    );
    tableBody.innerHTML = "";

    if (availableGroups.length === 0) {
        displayNoGroupsMessage(tableBody);
        return;
    }

    availableGroups.forEach((group) => addgroupRow(tableBody, group));
    groups_modal.showModal();
}

function displayNoGroupsMessage(tableBody) {
    const clone = noGroupsTemplate.content.cloneNode(true);
    tableBody.appendChild(clone);
    groups_modal.showModal();
}

function addgroupRow(tableBody, group) {
    const clone = template.content.cloneNode(true);

    const groupNameField = clone.querySelector(".group-name-field");
    groupNameField.innerText = group.name;

    const addButton = clone.querySelector(".add-group-button");
    addButton.setAttribute("data-group-id", group.id);
    addButton.addEventListener("click", () => handleAddgroup(group));

    tableBody.appendChild(clone);
}

function handleAddgroup(group) {
    // Move the group from availableGroups to associatedGroups
    availableGroups = availableGroups.filter(
        (availablegroup) => availablegroup.id !== group.id
    );
    associatedGroups.push(group);

    // Update the tables
    populateGroupsTable();
    updateAssociatedGroupsTable();
}

function updateAssociatedGroupsTable() {
    const tableBody = document.querySelector("#associated-groups-table-body");
    tableBody.innerHTML = "";

    associatedGroups.forEach((group) => {
        const clone = associatedTemplate.content.cloneNode(true);

        const groupNameField = clone.querySelector(".group-name-field");
        groupNameField.innerText = group.name;

        const removeButton = clone.querySelector(".remove-group-button");
        removeButton.setAttribute("data-group-id", group.id);
        removeButton.addEventListener("click", () => handleRemovegroup(group));

        tableBody.appendChild(clone);
    });
}

function handleRemovegroup(group) {
    // Move the group from associatedGroups to availableGroups
    associatedGroups = associatedGroups.filter(
        (associatedgroup) => associatedgroup.id !== group.id
    );
    availableGroups.push(group);

    // Update the tables
    populateGroupsTable();
    updateAssociatedGroupsTable();
}

// Salva le aziende associate

const saveButton = document.querySelector("#save-groups-button");

saveButton.addEventListener("click", async () => {
    let user_id = document.querySelector("#user_id").value;

    let formData = new FormData();
    const groupsToAssociate = associatedGroups.map((group) => group.id);
    formData.append("groups", JSON.stringify(groupsToAssociate));

    try {
        const response = await axios.post(
            `/admin/personnel/groups/users/${user_id}/associate-groups`,
            formData
        );

        if (response.status === 200) {
            window.location.reload();
        }
    } catch (error) {
        console.error("Error associating groups:", error);
    }
});
