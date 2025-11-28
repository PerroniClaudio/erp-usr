document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteProtocolModal');
    const form = document.getElementById('deleteProtocolForm');
    const routeTemplateInput = document.getElementById('deleteProtocolRouteTemplate');
    const deleteRouteTemplate = routeTemplateInput?.value;

    if (!modal || !form || !deleteRouteTemplate) {
        return;
    }

    document.querySelectorAll('[data-delete-protocol-id]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-delete-protocol-id');
            form.action = deleteRouteTemplate.replace(':id', id);
            modal.showModal();
        });
    });

    document.querySelectorAll('[data-close-delete-protocol-modal]').forEach((btn) => {
        btn.addEventListener('click', () => modal.close());
    });
});
