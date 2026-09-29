const adminUserInit = () => {
    const alertEl = document.getElementById('admin-alert');

    window.showAdminAlert = (msg, ok) => {
        // Affiche un message en haut de la page
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.className = 'mb-4 rounded-lg px-4 py-2 test-sm font-semibold alert-' + (ok ? 'success' : 'error');
        alertEl.classList.remove('hidden');
    };

    window.changeRole = async (id, role) => {
        // Change le rôle d'un utilisateur
        if (!role || !confirm('Changer le rôle ?')) return;
        const data = await apiFetch(BASE_URL + 'admin/utilisateurs/' + id + '/role', 'POST', { role });
        window.showAdminAlert?.(data.message, data.success);
        if (data.success) setTimeout(() => location.reload(), 1000);
    };

    window.deleteUser = async (id) => {
        // Supprime un utilisateur
        const data = await apiFetch(BASE_URL + 'admin/utilisateurs/' + id + '/supprimer');
        window.showAdminAlert?.(data.message, data.success);
        if (data.success) setTimeout(() => location.reload(), 1000);
    };

    window.viewUser = (id, pseudo, email, role) => {
        window.showAdminAlert?.(`Utilisateur #${id} - ${pseudo} (${email}) - ${role}`, true);
    };
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', adminUserInit);
} else {
    adminUserInit();
}