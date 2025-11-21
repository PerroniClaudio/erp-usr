const rolesModal = document.getElementById('roles-modal');

if (rolesModal) {
    const badgeColors = JSON.parse(rolesModal.dataset.badgeColors || '{}');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const modalTitle = document.getElementById('roles-modal-title');
    const titleTemplate = rolesModal.dataset.titleTemplate || 'Ruoli di :name';
    const errorMessage = rolesModal.dataset.errorMessage || 'Errore nella modifica dei ruoli';
    const emptyAvailable = rolesModal.dataset.emptyAvailable || 'Nessun ruolo disponibile.';
    const emptyAssigned = rolesModal.dataset.emptyAssigned || 'Nessun ruolo assegnato.';
    const availableBody = document.getElementById('available-roles-body');
    const assignedBody = document.getElementById('assigned-roles-body');

    let currentUserId = null;
    let availableRoles = [];
    let assignedRoles = [];

    const renderRoles = () => {
        availableBody.innerHTML = '';
        assignedBody.innerHTML = '';

        if (!availableRoles.length) {
            availableBody.innerHTML = `<tr><td class="text-sm text-base-content/60" colspan="2">${emptyAvailable}</td></tr>`;
        } else {
            availableRoles.forEach((role) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="badge ${badgeColors[role] ?? 'badge-ghost'}">${role}</span></td>
                    <td class="w-16 text-right">
                        <button class="btn btn-xs btn-primary" data-action="assign" data-role="${role}">+</button>
                    </td>`;
                availableBody.appendChild(tr);
            });
        }

        if (!assignedRoles.length) {
            assignedBody.innerHTML = `<tr><td class="text-sm text-base-content/60" colspan="2">${emptyAssigned}</td></tr>`;
        } else {
            assignedRoles.forEach((role) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="badge ${badgeColors[role] ?? 'badge-ghost'}">${role}</span></td>
                    <td class="w-16 text-right">
                        <button class="btn btn-xs btn-outline" data-action="remove" data-role="${role}">-</button>
                    </td>`;
                assignedBody.appendChild(tr);
            });
        }
    };

    const toggleRole = async (action, role) => {
        if (!currentUserId) return;

        const url = action === 'assign'
            ? `/admin/personnel/users/${currentUserId}/assign-role`
            : `/admin/personnel/users/${currentUserId}/remove-role`;

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ role }),
        });

        if (!res.ok) {
            // eslint-disable-next-line no-alert
            alert(errorMessage);
            return;
        }

        if (action === 'assign') {
            assignedRoles = Array.from(new Set([...assignedRoles, role]));
            availableRoles = availableRoles.filter((r) => r !== role);
        } else {
            availableRoles = Array.from(new Set([...availableRoles, role]));
            assignedRoles = assignedRoles.filter((r) => r !== role);
        }

        renderRoles();
    };

    document.addEventListener('click', (event) => {
        const openBtn = event.target.closest('.open-role-modal');

        if (openBtn) {
            currentUserId = openBtn.dataset.userId;
            modalTitle.textContent = titleTemplate.replace(':name', openBtn.dataset.userName);
            availableRoles = JSON.parse(openBtn.dataset.available || '[]');
            assignedRoles = JSON.parse(openBtn.dataset.assigned || '[]');
            renderRoles();
            rolesModal.showModal();
            return;
        }

        if (event.target.matches('[data-action="assign"]')) {
            const role = event.target.dataset.role;
            toggleRole('assign', role);
        }

        if (event.target.matches('[data-action="remove"]')) {
            const role = event.target.dataset.role;
            toggleRole('remove', role);
        }
    });
}
