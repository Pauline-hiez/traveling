const contactInit = () => {
    const form = document.getElementById('form-contact');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        // Envoi du formulaire de contact
        const data = await apiFetch(BASE_URL + 'contact', 'POST', Object.fromEntries(new FormData(form)));
        const alertBox = document.getElementById('contact-alert');
        if (!alertBox) return;

        alertBox.textContent = data.message;
        alertBox.className = 'mb-4 rounded lg px-4 py-2 text-sm font-semibold alert-' + (data.success ? 'success' : 'error');
        alertBox.classList.remove('hidden');
        if (data.success) form.reset();
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', contactInit);
} else {
    contactInit;
}