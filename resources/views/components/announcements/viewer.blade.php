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
        fetch('/standard/announcements/unread')
            .then(response => response.json())
            .then(announcements => {
                if (announcements.length > 0) {
                    currentAnnouncements = announcements;
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

            titleElement.textContent = announcement.title;
            contentElement.innerHTML = announcement.content.replace(/\n/g, '<br>');

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
            fetch(`/standard/announcements/${announcementId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
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
