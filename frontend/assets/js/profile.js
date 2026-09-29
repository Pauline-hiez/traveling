// Upload de l'avatar ou du fond de profil (soumission AJAX du formulaire)
async function uploadProfileImage(form) {
    const data = await apiFetch(form.action, 'POST', new FormData(form));
    if (!data.success) {
        alert(data.message || 'Erreur lors de la mise à jour.');
        return;
    }
    // Recharge la page pour afficher la nouvelle image et ses positions
    location.reload();
}

// Ouverture/fermeture des modales d'actions du profil (email, pseudo, mot de passe)
window.openModal = (id) => {
    document.getElementById(id)?.classList.add('open');
};

window.closeModal = (id) => {
    document.getElementById(id)?.classList.remove('open');
};

// Suppression du compte
window.deleteAccount = async () => {
    const data = await apiFetch(BASE_URL + 'profil/supprimer');
    if (data.success && data.redirect) {
        window.location.href = data.redirect;
    } else {
        alert(data.message || 'Erreur lors de la suppression du compte.');
    }
};

// Soumission générique des formulaires de modification du profil
const profileForms = {
    'form-email': BASE_URL + 'profil/email',
    'form-pseudo': BASE_URL + 'profil/pseudo',
    'form-password': BASE_URL + 'profil/password',
};

Object.entries(profileForms).forEach(([formId, url]) => {
    const form = document.getElementById(formId);
    const alertEl = document.getElementById(formId + '-alert');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = await apiFetch(url, 'POST', new FormData(form));

        if (alertEl) {
            alertEl.textContent = data.message || '';
            alertEl.className = 'mb-3 rounded-lg px-3 py-2 text-sm alert-' + (data.success ? 'success' : 'error');
            alertEl.classList.remove('hidden');
        }

        if (data.success) {
            setTimeout(() => location.reload(), 1000);
        }
    });
});
