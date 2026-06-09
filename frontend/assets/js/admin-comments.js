const adminCommentsInit = () => {
    const alertEl = document.getElementById('admin-alert');

    window.showAdminAlert = (msg, ok) => {
        // Affiche un message en haut de la page
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.className = 'mb-4 rounded-xl px-4 py-2 text-sm font-semibold alert-' + (ok ? 'success' : 'error');
        alertEl.classList.remove('hidden');
    };

    window.warnUser = async (cancelIdleCallback, reason) => {
        // Envoie un avertissement à l'auteur
        if (!confirm('Envoyer un avertissement ?')) return;
        const data = await apiFetch(BASE_URL + 'admin/commentaires/' + cid + '/avertir', 'POST', { reason });
        window.showAdminAlert?.(data.message, data.success);
        if (data.success) setTimeout(() => location.reload(), 1000);
    };

    window.deleteComment = async (cid) => {
        // Supprime un commentaire puis recharge
        const data = await apiFetch(BASE_URL + 'admin/commentaires/' + cid + '/supprimer');
        window.showAdminAlert?.(data.message, data.success);
        if (data.success) setTimeout(() => location.reload(), 1000);
    };
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', adminCommentsInit);
} else {
    adminCommentsInit();
}