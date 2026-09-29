const articleInit = () => {
    const main = document.querySelector('main[data-article-id]');
    const articleId = main ? main.dataset.articleId : null;

    const modalComment = document.getElementById('modal-comment');
    const modalReport = document.getElementById('modal-report');
    const commentParentId = document.getElementById('comment-parent-id');
    const reportCid = document.getElementById('report-cid');
    const formComment = document.getElementById('form-comment');
    const formReport = document.getElementById('form-report');
    const commentAlert = document.getElementById('comment-alert');

    const showCommentAlert = (msg, ok) => {
        if (!commentAlert) return;
        commentAlert.textContent = msg;
        commentAlert.className = 'mb-3 rounded-lg px-3 py-2 text-sm alert-' + (ok ? 'success' : 'error');
        commentAlert.classList.remove('hidden');
    };

    window.openComment = () => {
        // Ouvre la modale pour un nouveau commentaire
        if (!modalComment) return;
        if (commentParentId) commentParentId.value = '';
        modalComment.classList.add('open');
    };

    window.openReply = (commentId) => {
        // Ouvre la modale pour répondre à un commentaire
        if (!modalComment) return;
        if (commentParentId) commentParentId.value = commentId;
        modalComment.classList.add('open');
    };

    window.openReport = (commentId) => {
        // Ouvre la modale de signalement
        if (!modalReport) return;
        if (reportCid) reportCid.value = commentId;
        modalReport.classList.add('open');
    };

    formComment?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!articleId) return;
        const data = await apiFetch(BASE_URL + 'commentaires/' + articleId + '/creer', 'POST', new FormData(formComment));
        if (data.success) {
            location.reload();
        } else {
            showCommentAlert(data.message || 'Erreur lors de l\'envoi.', false);
        }
    });

    formReport?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const cid = reportCid ? reportCid.value : '';
        if (!cid) return;
        const data = await apiFetch(BASE_URL + 'commentaires/' + cid + '/signaler', 'POST', new FormData(formReport));
        alert(data.message || (data.success ? 'Signalement envoyé.' : 'Erreur.'));
        if (data.success) modalReport?.classList.remove('open');
    });

    document.getElementById('btn-more-comments')?.addEventListener('click', function () {
        // Affiche tous les commentaires restants
        document.querySelectorAll('.comment.hidden').forEach((el) => el.classList.remove('hidden'));
        this.remove();
    });

    // Carte Leaflet des lieux de tournage associés à l'article
    const mapEl = document.getElementById('map');
    if (mapEl && typeof L !== 'undefined') {
        let lieux = [];
        try {
            lieux = JSON.parse(mapEl.dataset.lieux || '[]');
        } catch {
            lieux = [];
        }

        const points = lieux.filter((l) => l && l.lat && l.lng);
        if (points.length) {
            const map = L.map('map').setView([points[0].lat, points[0].lng], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
            }).addTo(map);

            const markers = points.map((p) => L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name || 'Lieu'));
            if (markers.length > 1) {
                map.fitBounds(L.featureGroup(markers).getBounds(), { padding: [20, 20] });
            }
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', articleInit);
} else {
    articleInit();
}
