<div id="announcements-container">
    <!-- Gli annunci verranno caricati qui via JavaScript -->
</div>

<!-- Modal per visualizzare gli annunci -->
<dialog id="announcement-modal" class="modal">
    <div class="modal-box max-w-2xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 id="announcement-title" class="font-bold text-lg mb-4"></h3>
        <div id="announcement-content" class="py-4"></div>
        <div id="announcement-attachments" class="mt-4 hidden">
            <h4 class="font-semibold mb-2">Allegati</h4>
            <ul class="list-disc list-inside space-y-1"></ul>
        </div>
        <div class="modal-action">
            <button id="mark-as-read-btn" class="btn btn-primary">
                Ho letto l'annuncio
            </button>
        </div>
    </div>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentAnnouncements = [];
        let currentIndex = 0;

        // Carica gli annunci non letti
        axios
            .get('/standard/announcements/unread')
            .then(({ data }) => {
                if (data.length > 0) {
                    currentAnnouncements = data;
                    showNextAnnouncement();
                }
            })
            .catch(error => console.error('Errore nel caricamento degli annunci:', error));

        function showNextAnnouncement() {
            if (currentIndex < currentAnnouncements.length) {
                const announcement = currentAnnouncements[currentIndex];
                showAnnouncementModal(announcement);
            }
        }

        function showAnnouncementModal(announcement) {
            const modal = document.getElementById('announcement-modal');
            const titleElement = document.getElementById('announcement-title');
            const contentElement = document.getElementById('announcement-content');
            const markAsReadBtn = document.getElementById('mark-as-read-btn');
            const attachmentsWrapper = document.getElementById('announcement-attachments');
            const attachmentsList = attachmentsWrapper?.querySelector('ul');

            titleElement.textContent = announcement.title;
            contentElement.innerHTML = announcement.content.replace(/\n/g, '<br>');

            if (attachmentsWrapper && attachmentsList) {
                attachmentsList.innerHTML = '';
                if (announcement.attachments && announcement.attachments.length > 0) {
                    attachmentsWrapper.classList.remove('hidden');
                    announcement.attachments.forEach(file => {
                        const li = document.createElement('li');
                        const link = document.createElement('a');
                        link.href = file.download_url || `/files/${file.id}/download`;
                        link.textContent = file.name || 'Allegato';
                        link.className = 'link link-primary';
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';

                        if (file.mime_type) {
                            const badge = document.createElement('span');
                            badge.className = 'ml-2 text-xs text-base-content/60';
                            badge.textContent = file.mime_type;
                            li.appendChild(badge);
                        }

                        li.prepend(link);
                        attachmentsList.appendChild(li);
                    });
                } else {
                    attachmentsWrapper.classList.add('hidden');
                }
            }

            // Rimuovi event listener precedenti
            const newMarkAsReadBtn = markAsReadBtn.cloneNode(true);
            markAsReadBtn.parentNode.replaceChild(newMarkAsReadBtn, markAsReadBtn);

            // Aggiungi nuovo event listener
            newMarkAsReadBtn.addEventListener('click', function() {
                markAnnouncementAsRead(announcement.id);
            });

            modal.showModal();
        }

        function markAnnouncementAsRead(announcementId) {
            axios
                .post(`/standard/announcements/${announcementId}/mark-as-read`)
                .then(({ data }) => {
                    if (data.success) {
                        const modal = document.getElementById('announcement-modal');
                        modal.close();

                        currentIndex++;
                        // Mostra il prossimo annuncio dopo un breve delay
                        setTimeout(showNextAnnouncement, 500);
                    }
                })
                .catch(error => {
                    console.error('Errore nel marcare l\'annuncio come letto:', error);
                });
        }
    });
</script>
