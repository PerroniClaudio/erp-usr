const openModalButton = document.getElementById("associate-users-modal-opener");
const userListItemTemplate = document.getElementById("user-list-item-template");
const selectedUserListItemTemplate = document.getElementById(
    "selected-user-list-item-template"
);
const saveUserAssociationButton = document.getElementById("save-association");
let usersToAssociate = [];
let unassociatedUsers = [];

openModalButton.addEventListener("click", async () => {
    await fetchUnassociatedUsers();
    associate_user.showModal();
});

async function fetchUnassociatedUsers() {
    const companyId = document.getElementById("company_id").value;

    const { data } = await axios.get(
        `/admin/personnel/companies/${companyId}/available-users`
    );
    unassociatedUsers = data.users;
    renderUnassociatedUsers();
}

function renderUnassociatedUsers() {
    const userListTableBody = document.getElementById("user-list");
    userListTableBody.innerHTML = "";

    unassociatedUsers
        .sort((a, b) => a.id - b.id)
        .forEach((user) => {
            const tableRow = userListItemTemplate.content
                .cloneNode(true)
                .querySelector("tr");
            tableRow.querySelector(".user-id-field").textContent = user.id;
            tableRow.querySelector(".user-name-field").textContent = user.name;
            tableRow.querySelector(".user-email-field").textContent =
                user.email;
            tableRow
                .querySelector(".btn")
                .setAttribute("data-user-id", user.id);

            const userListTbody = document.querySelector("tbody#user-list");
            userListTbody.appendChild(tableRow);
        });
}

function renderAssociatedUsers() {
    const associatedUserListTableBody =
        document.getElementById("selected-user-list");
    associatedUserListTableBody.innerHTML = "";

    usersToAssociate
        .sort((a, b) => a.id - b.id)
        .forEach((user) => {
            const tableRow = selectedUserListItemTemplate.content
                .cloneNode(true)
                .querySelector("tr");
            tableRow.querySelector(".user-id-field").textContent = user.id;
            tableRow.querySelector(".user-name-field").textContent = user.name;
            tableRow.querySelector(".user-email-field").textContent =
                user.email;
            tableRow
                .querySelector(".btn")
                .setAttribute("data-user-id", user.id);

            const selectedUserListTbody = document.querySelector(
                "tbody#selected-user-list"
            );
            selectedUserListTbody.appendChild(tableRow);
        });
}

document.addEventListener("click", (event) => {
    if (event.target.classList.contains("add-user-button")) {
        const userId = event.target.getAttribute("data-user-id");

        const userIndex = unassociatedUsers.findIndex((user) => {
            return user.id == userId;
        });

        if (userIndex !== -1) {
            const user = unassociatedUsers.splice(userIndex, 1)[0];
            usersToAssociate.push(user);
            renderUnassociatedUsers();
            renderAssociatedUsers();
        }
    }
    if (event.target.classList.contains("remove-user-button")) {
        const userId = event.target.getAttribute("data-user-id");

        const userIndex = usersToAssociate.findIndex((user) => {
            return user.id == userId;
        });

        if (userIndex !== -1) {
            const user = usersToAssociate.splice(userIndex, 1)[0];
            unassociatedUsers.push(user);
            renderUnassociatedUsers();
            renderAssociatedUsers();
        }
    }
});

saveUserAssociationButton.addEventListener("click", async () => {
    const companyId = document.getElementById("company_id").value;
    const formData = new FormData();
    const userIdsToAssociate = usersToAssociate.map((user) => user.id);
    formData.append("users", JSON.stringify(userIdsToAssociate));

    try {
        const response = await axios.post(
            `/admin/personnel/companies/${companyId}/associate-users`,
            formData
        );

        if (response.status === 200) {
            window.location.reload();
        } else {
            console.error("Failed to save user associations");
        }
    } catch (error) {
        console.error("Error saving user associations:", error);
    }
});
