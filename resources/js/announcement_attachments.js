document.addEventListener('DOMContentLoaded', () => {
    const attachmentModal = document.getElementById('attachments-modal');
    if (!attachmentModal) return;

    const selectedContainer = document.getElementById('selected-attachments');
    const resultsContainer = document.getElementById('attachment-search-results');
    const searchInput = document.getElementById('attachment-search-input');
    const searchButton = document.getElementById('attachment-search-button');
    const counter = document.getElementById('attachment-counter');
    const openModalBtn = document.getElementById('open-attachment-modal');
    const closeModalBtn = document.getElementById('close-attachment-modal');
    const confirmAttachments = document.getElementById('confirm-attachments');
    const cancelAttachments = document.getElementById('cancel-attachments');
    const newAttachmentInput = document.querySelector('input[name="new_attachment"]');
    const modeButtons = document.querySelectorAll('.attachment-mode-button');
    const pendingExisting = new Map();
    let lastSearchResults = [];

    const pendingFeedback = document.getElementById('pending-existing-feedback');
    const pendingList = pendingFeedback?.querySelector('[data-pending-list]');
    const pendingPlaceholder = pendingFeedback?.querySelector('[data-pending-placeholder]');

    const panels = {
        search: document.getElementById('search-panel'),
        upload: document.getElementById('upload-panel'),
    };

    function getItemsCount() {
        const inputs = Array.from(selectedContainer?.querySelectorAll('input[name="attachment_file_ids[]"]') || []);
        const uniqueIds = new Set(inputs.map(input => input.value).filter(Boolean));
        const previews = selectedContainer?.querySelectorAll('[data-upload-preview]').length || 0;
        return uniqueIds.size + previews;
    }

    function updateCounter() {
        if (!counter || !selectedContainer) return;
        const count = getItemsCount();
        counter.textContent = count ? `${count} allegato${count > 1 ? 'i' : ''}` : '';
    }

    function ensureEmptyState() {
        if (!selectedContainer) return;
        const hasItems = getItemsCount() > 0;
        const empty = selectedContainer.querySelector('[data-empty-attachments]');

        if (hasItems) {
            empty?.remove();
        } else if (!empty) {
            const p = document.createElement('p');
            p.className = 'text-sm text-base-content/60';
            p.dataset.emptyAttachments = 'true';
            p.textContent = 'Nessun allegato selezionato.';
            selectedContainer.appendChild(p);
        }

        updateCounter();
    }

    ensureEmptyState();

    function cloneTemplate(id) {
        const tpl = document.getElementById(id);
        if (!tpl?.content?.firstElementChild) return null;
        return tpl.content.firstElementChild.cloneNode(true);
    }

    function mimeToIcon(mime) {
        if (!mime) return 'default';
        if (mime.startsWith('image/')) return 'image';
        if (mime.startsWith('video/')) return 'video';
        if (mime.startsWith('audio/')) return 'audio';
        if (mime === 'application/pdf' || mime.includes('word') || mime.includes('document')) return 'document';
        if (mime.includes('zip') || mime.includes('compressed')) return 'archive';
        if (mime.includes('excel') || mime.includes('spreadsheet')) return 'document';
        return 'default';
    }

    function setIcon(container, mime) {
        if (!container) return;
        const desired = mimeToIcon(mime);
        container.querySelectorAll('[data-icon-type]').forEach(icon => {
            const type = icon.getAttribute('data-icon-type');
            icon.classList.toggle('hidden', type !== desired);
        });
    }

    function addAttachmentFromDataset(button) {
        if (!selectedContainer) return;
        const id = button.dataset.attachmentId;
        if (!id || selectedContainer.querySelector(`[data-attachment-id="${id}"]`)) {
            return;
        }

        const name = button.dataset.attachmentName || 'File';
        const mime = button.dataset.attachmentMime || '';
        const size = button.dataset.attachmentSize || '';

        const wrapper = cloneTemplate('tpl-selected-attachment');
        if (!wrapper) return;
        wrapper.dataset.attachmentId = id;

        const nameTarget = wrapper.querySelector('[data-role="name"]');
        const metaTarget = wrapper.querySelector('[data-role="meta"]');
        const removeBtn = wrapper.querySelector('[data-role="remove-btn"]');
        const hidden = wrapper.querySelector('[data-role="hidden-id"]');
        const downloadLink = wrapper.querySelector('[data-role="download"]');
        const iconTarget = wrapper.querySelector('[data-role="icon"]');

        if (nameTarget) nameTarget.textContent = name;
        if (metaTarget) metaTarget.textContent = [mime, size].filter(Boolean).join(' • ');
        setIcon(iconTarget, mime);
        if (removeBtn) removeBtn.dataset.attachmentId = id;
        if (hidden) hidden.value = id;
        if (downloadLink) {
            const url = button.dataset.downloadUrl || `/files/${id}/download`;
            downloadLink.href = url;
            if (button.dataset.hideDownload === 'true') {
                downloadLink.classList.add('hidden');
            } else {
                downloadLink.classList.remove('hidden');
            }
        }

        selectedContainer.appendChild(wrapper);
        ensureEmptyState();
    }

    function isAlreadySelected(id) {
        return !!selectedContainer?.querySelector(`[data-attachment-id="${id}"]`);
    }

    function addExistingAttachment(entry) {
        if (isAlreadySelected(entry.id)) {
            return;
        }

        const pseudoButton = document.createElement('button');
        pseudoButton.dataset.attachmentId = entry.id;
        pseudoButton.dataset.attachmentName = entry.name;
        pseudoButton.dataset.attachmentMime = entry.mime_type || '';
        pseudoButton.dataset.attachmentSize = entry.human_file_size || '';
        addAttachmentFromDataset(pseudoButton);
    }

    selectedContainer?.addEventListener('click', event => {
        const button = event.target.closest('.remove-attachment');
        if (!button) {
            const removeUploadBtn = event.target.closest('.remove-upload-preview');
            if (removeUploadBtn) {
                removeUploadPreview();
                clearPendingExisting();
            }
            return;
        }

        const id = button.dataset.attachmentId;
        const row = selectedContainer.querySelector(`[data-attachment-id="${id}"]`);
        row?.remove();
        pendingExisting.delete(id);
        renderPendingExisting();
        renderSearchResults(lastSearchResults);
        ensureEmptyState();
    });

    function renderSearchResults(files) {
        lastSearchResults = files;
        if (!resultsContainer) return;
        resultsContainer.innerHTML = '';

        if (!files.length) {
            resultsContainer.innerHTML = '<p class="text-sm text-base-content/60">Nessun file trovato.</p>';
            return;
        }

        files.forEach(file => {
            const row = cloneTemplate('tpl-search-result');
            if (!row) return;

            const nameTarget = row.querySelector('[data-role="name"]');
            const metaTarget = row.querySelector('[data-role="meta"]');
            const addButton = row.querySelector('[data-role="action"]');
            const iconTarget = row.querySelector('[data-role="icon"]');

            if (nameTarget) nameTarget.textContent = file.name || 'File';
            if (metaTarget) {
                const details = [file.mime_type || '', file.human_file_size || ''].filter(Boolean).join(' • ');
                metaTarget.textContent = details;
            }
            setIcon(iconTarget, file.mime_type || '');

            const isPending = pendingExisting.has(file.id);
            const alreadyAdded = isAlreadySelected(file.id);
            if (addButton) {
                addButton.type = 'button';
                addButton.className = `btn btn-xs ${alreadyAdded ? 'btn-ghost' : isPending ? 'btn-secondary' : 'btn-primary'}`;
                addButton.dataset.attachmentId = file.id;
                addButton.dataset.attachmentName = file.name || 'File';
                addButton.dataset.attachmentMime = file.mime_type || '';
                addButton.dataset.attachmentSize = file.human_file_size || '';
                addButton.textContent = alreadyAdded ? 'Già aggiunto' : isPending ? 'Selezionato' : 'Aggiungi';
                addButton.disabled = alreadyAdded;
            }

            resultsContainer.appendChild(row);
        });

        renderPendingExisting();
    }

    function performSearch() {
        if (!searchInput || !resultsContainer) return;

        const query = searchInput.value.trim();
        if (!query) {
            resultsContainer.innerHTML = '<p class="text-sm text-base-content/60">Inserisci un termine di ricerca.</p>';
            return;
        }

        resultsContainer.innerHTML = '<p class="text-sm text-base-content/60">Ricerca in corso...</p>';

        const searchUrl = searchButton?.dataset.searchUrl || '/admin/files/search';

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            headers: {
                Accept: 'application/json',
            },
        })
            .then(response => response.json())
            .then(payload => renderSearchResults(payload.data || []))
            .catch(() => {
                resultsContainer.innerHTML = '<p class="text-sm text-error">Errore nella ricerca degli allegati.</p>';
            });
    }

    searchButton?.addEventListener('click', performSearch);
    searchInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            performSearch();
        }
    });

    resultsContainer?.addEventListener('click', event => {
        const button = event.target.closest('button[data-attachment-id]');
        if (button) {
            if (button.disabled) return;
            const id = button.dataset.attachmentId;
            if (!id) return;
            const entry = {
                id,
                name: button.dataset.attachmentName || 'File',
                mime_type: button.dataset.attachmentMime || '',
                human_file_size: button.dataset.attachmentSize || '',
            };

            if (pendingExisting.has(id)) {
                pendingExisting.delete(id);
            } else {
                pendingExisting.set(id, entry);
            }

            renderSearchResults(lastSearchResults);
        }
    });

    openModalBtn?.addEventListener('click', () => {
        clearPendingExisting();
        clearUploadPreview();
        attachmentModal?.showModal();
    });

    closeModalBtn?.addEventListener('click', () => {
        clearUploadPreview();
        attachmentModal?.close();
    });

    cancelAttachments?.addEventListener('click', () => {
        clearPendingExisting();
        attachmentModal?.close();
    });

    confirmAttachments?.addEventListener('click', () => {
        syncUploadPreview();
        pendingExisting.forEach(entry => addExistingAttachment(entry));
        pendingExisting.clear();
        renderSearchResults(lastSearchResults);
        attachmentModal?.close();
    });

    function formatBytes(bytes) {
        if (!bytes) return '';
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let idx = 0;
        while (size >= 1024 && idx < units.length - 1) {
            size /= 1024;
            idx++;
        }
        return `${size.toFixed(size >= 10 ? 0 : 1)} ${units[idx]}`;
    }

    function removeUploadPreview(resetInput = true) {
        const preview = selectedContainer?.querySelector('[data-upload-preview]');
        if (preview) {
            preview.remove();
        }
        if (newAttachmentInput && resetInput) {
            newAttachmentInput.value = '';
        }
        ensureEmptyState();
    }

    function clearUploadPreview() {
        removeUploadPreview(true);
    }

    function syncUploadPreview() {
        if (!selectedContainer || !newAttachmentInput) return;
        removeUploadPreview(false);

        const file = newAttachmentInput.files?.[0];
        if (!file) {
            return;
        }

        const wrapper = cloneTemplate('tpl-upload-preview');
        if (!wrapper) return;
        wrapper.dataset.uploadPreview = 'true';

        const nameTarget = wrapper.querySelector('[data-role="name"]');
        const metaTarget = wrapper.querySelector('[data-role="meta"]');
        const iconTarget = wrapper.querySelector('[data-role="icon"]');

        if (nameTarget) nameTarget.textContent = file.name;
        if (metaTarget) {
            metaTarget.textContent = [file.type || 'file', formatBytes(file.size) || null, 'verrà caricato al salvataggio']
                .filter(Boolean)
                .join(' • ');
        }
        setIcon(iconTarget, file.type || '');

        selectedContainer.appendChild(wrapper);
        ensureEmptyState();
    }

    function renderPendingExisting() {
        if (!pendingFeedback || !pendingList || !pendingPlaceholder) return;

        pendingList.innerHTML = '';
        const entries = Array.from(pendingExisting.values());

        if (!entries.length) {
            pendingPlaceholder.classList.remove('hidden');
            return;
        }

        pendingPlaceholder.classList.add('hidden');

        entries.forEach(entry => {
            const badge = cloneTemplate('tpl-pending-badge');
            if (!badge) return;
            badge.dataset.pendingId = entry.id;

            const nameTarget = badge.querySelector('[data-role="name"]');
            const removeBtn = badge.querySelector('[data-role="remove"]');
            const iconTarget = badge.querySelector('[data-role="icon"]');
            if (nameTarget) nameTarget.textContent = entry.name || 'File';
            if (removeBtn) removeBtn.dataset.pendingId = entry.id;
            setIcon(iconTarget, entry.mime_type || '');

            pendingList.appendChild(badge);
        });
    }

    pendingFeedback?.addEventListener('click', event => {
        const button = event.target.closest('button[data-pending-id]');
        if (!button) return;
        const id = button.dataset.pendingId;
        if (!id) return;
        pendingExisting.delete(id);
        renderSearchResults(lastSearchResults);
    });

    function clearPendingExisting() {
        pendingExisting.clear();
        renderPendingExisting();
    }

    function switchPanel(target) {
        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) return;
            const isActive = key === target;
            panel.classList.toggle('hidden', !isActive);
        });

        modeButtons.forEach(btn => {
            const isActive = btn.dataset.targetPanel === `${target}-panel`;
            btn.classList.toggle('btn-primary', isActive);
            btn.classList.toggle('btn-ghost', !isActive);
        });
    }

    modeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const panel = btn.dataset.targetPanel?.replace('-panel', '');
            if (panel) {
                switchPanel(panel);
            }
        });
    });

    switchPanel('search');
});
