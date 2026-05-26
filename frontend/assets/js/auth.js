(function () {
    // Elements du modal d'authentification
    const overlay = document.getElementById('modal-auth');
    const btnOpen = document.getElementById('btn-auth');
    const btnClose = document.getElementById('modal-auth-close');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const panelLogin = document.getElementById('panel-login');
    const panelReg = document.getElementById('panel-register');
    const formLogin = document.getElementById('form-login');
    const formForgot = document.getElementById('form-forgot');
    const formRegister = document.getElementById('form-register');
    const goForgot = document.getElementById('go-forgot');
    const goBackLogin = document.getElementById('go-back-login');
    let alertEl = document.getElementById('auth-alert');
    if (!overlay) return;

    if (!alertEl) {
        // Cree une zone d'alerte si absente
        alertEl = document.createElement('div');
        alertEl.id = 'auth-alert';
        alertEl.className = 'hidden';
        const modal = overlay.querySelector('.modal');
        if (modal) {
            modal.insertBefore(alertEl, modal.children[1] ?? null);
        }
    }

    // Ouvrir/fermer
    btnOpen?.addEventListener('click', () => overlay.classList.add('open'));
    btnClose?.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    function closeModal() {
        // Ferme le modal et nettoie l'alerte
        overlay.classList.remove('open'); clearAlert();
    }

    const openOnLogin = new URLSearchParams(window.location.search).has('login');
    if (openOnLogin) overlay.classList.add('open');

    // Onglets
    tabLogin?.addEventListener('click', () => showPanel('login'));
    tabRegister?.addEventListener('click', () => showPanel('register'));

    function showPanel(p) {
        // Affiche l'onglet courant
        const isLogin = p === 'login';
        panelLogin.classList.toggle('hidden', !isLogin);
        panelReg.classList.toggle('hidden', isLogin);
        tabLogin.classList.toggle('border-b-2', isLogin);
        tabRegister.classList.toggle('border-b-2', !isLogin);
        tabLogin.style.borderColor = isLogin ? 'var(--gold-bright)' : 'transparent';
        tabRegister.style.borderColor = isLogin ? 'transparent' : 'var(--gold-bright)';
        tabLogin.style.opacity = isLogin ? '1' : '0.55';
        tabRegister.style.opacity = isLogin ? '0.55' : '1';
        hideForgotForm();
        clearAlert();
    }
    showPanel(openOnLogin ? 'login' : 'register');

    document.getElementById('go-register')?.addEventListener('click', e => { e.preventDefault(); showPanel('register'); });
    document.getElementById('go-login')?.addEventListener('click', e => { e.preventDefault(); showPanel('login'); });
    goForgot?.addEventListener('click', e => {
        // Affiche le formulaire "mot de passe oublie"
        e.preventDefault();
        formLogin?.classList.add('hidden');
        formForgot?.classList.remove('hidden');
        clearAlert();
    });
    goBackLogin?.addEventListener('click', e => {
        // Retour au formulaire de login
        e.preventDefault();
        hideForgotForm();
        clearAlert();
    });

    formLogin?.addEventListener('submit', e => {
        e.preventDefault();
        // Soumet la connexion
        submit(formLogin, BASE_URL + 'auth/connexion');
    });

    formRegister?.addEventListener('submit', e => {
        e.preventDefault();
        // Soumet l'inscription
        submit(formRegister, BASE_URL + 'auth/inscription');
    });

    formForgot?.addEventListener('submit', e => {
        e.preventDefault();
        // Soumet la demande de reset
        submit(formForgot, BASE_URL + 'auth/mot-de-passe-oublie');
    });

    function hideForgotForm() {
        // Cache le formulaire de reset
        formForgot?.classList.add('hidden');
        formLogin?.classList.remove('hidden');
    }

    async function submit(form, url) {
        // Envoi du formulaire et gestion des retours JSON
        clearAlert();
        try {
            const res = await fetch(url, { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            if (json.success) {
                showAlert(json.message, 'success');
                const redirectUrl = typeof json.redirect === 'string' && json.redirect
                    ? json.redirect
                    : (typeof json.data === 'string' ? json.data : '');
                if (redirectUrl) {
                    setTimeout(() => { window.location.href = redirectUrl; }, 1200);
                }
            } else {
                showAlert(json.message, 'error');
            }
        } catch { showAlert('Une erreur est survenue.', 'error'); }
    }

    function showAlert(msg, type) {
        // Affiche une alerte visuelle
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.className = 'rounded-lg px-4 py-2 text-sm font-semibold mb-4 alert-' + type;
        alertEl.classList.remove('hidden');
    }

    function clearAlert() {
        // Nettoie l'alerte
        if (!alertEl) return;
        alertEl.textContent = '';
        alertEl.className = 'hidden';
    }
})();