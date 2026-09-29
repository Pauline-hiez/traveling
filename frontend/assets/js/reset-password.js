const form = document.getElementById('form-reset-password');
const alertEl = document.getElementById('reset-password-alert');

form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = await apiFetch(form.action, 'POST', new FormData(form));

    if (alertEl) {
        alertEl.textContent = data.message || '';
        alertEl.className = 'mb-3 rounded-lg px-3 py-2 text-sm alert-' + (data.success ? 'success' : 'error');
        alertEl.classList.remove('hidden');
    }

    if (data.success && data.data) {
        setTimeout(() => { window.location.href = data.data; }, 1200);
    }
});
