document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteSectorModal');
    const form = document.getElementById('deleteSectorForm');
    const routeTemplateInput = document.getElementById('deleteSectorRouteTemplate');
    const deleteRouteTemplate = routeTemplateInput?.value;

    if (!modal || !form || !deleteRouteTemplate) {
        return;
    }

    document.querySelectorAll('[data-delete-sector-id]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-delete-sector-id');
            form.action = deleteRouteTemplate.replace(':id', id);
            modal.showModal();
        });
    });

    document.querySelectorAll('[data-close-delete-modal]').forEach((btn) => {
        btn.addEventListener('click', () => modal.close());
    });
});
