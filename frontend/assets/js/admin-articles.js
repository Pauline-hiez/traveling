const adminArticlesInit = () => {
    const alertEl = document.getElementById('admin-alert');

    window.showAdminAlert = (msg, ok) => {
        // Affiche un message en haut de la page
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.className = 'mb-4 rounded-lg px-4 py-2 text-sm font-semibold alert-' + (ok ? 'success' : 'error');
        alertEl.classList.remove('hidden');
    };

    window.deleteArticle = async (id) => {
        // Supprime un article puis recharge la liste
        const data = await apiFetch(BASE_URL + 'admin/articles/' + id + '/supprimer');
        window.showAdminAlert?.(data.message, data.success);
        if (data.success) setTimeout(() => location.reload(), 1000);
    };
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', adminArticlesInit);
} else {
    adminArticlesInit();
}