// Toggle "voir plus / voir moins"
document.querySelectorAll('[data-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.toggle);
        if (!target) return;
        const hidden = target.classList.toggle('hidden');
        btn.textContent = hidden ? 'Voir plus' : 'Voir moins';
    });
});

// Confirmation avant action destructive
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});

// Fetch AJAX generique avec CSRF
async function apiFetch(url, method = 'POST', body = {}) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const form = new FormData();
    form.append('csrf_token', csrf);
    if (body instanceof FormData) {
        body.forEach((value, key) => form.append(key, value));
    } else {
        Object.entries(body).forEach(([key, value]) => form.append(key, value));
    }

    // Envoie le cookie de session (necessaire pour CSRF)
    const res = await fetch(url, { method, body: form, credentials: 'same-origin' });
    // Tente un JSON, sinon renvoie le texte brut
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch (err) {
        return { success: false, message: 'Réponse non-JSON du serveur: ' + text };
    }
}

// Autocomplete generique pour les searchbars
const initSearchAutocomplete = () => {
    const inputs = Array.from(document.querySelectorAll('[data-autocomplete-endpoint]'));
    if (!inputs.length) return;

    const timers = new Map();

    const updateDatalist = (input, items) => {
        const listId = input.getAttribute('list');
        if (!listId) return;
        const datalist = document.getElementById(listId);
        if (!datalist) return;
        datalist.innerHTML = '';

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.label || item.title || item.name || '';
            datalist.appendChild(option);
        });
    };

    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const renderSuggestions = (input, items) => {
        const sel = input.dataset.suggestContainer;
        if (!sel) return;
        const container = document.querySelector(sel);
        if (!container) return;

        if (!items.length) {
            container.innerHTML = '';
            container.classList.add('hidden');
            return;
        }

        const type = input.dataset.autocompleteType || '';

        container.innerHTML = items.map(i => {
            const poster = i.poster ? escapeHtml(i.poster) : '';
            const imgHtml = poster
                ? `<img src="${poster}" alt="" class="w-12 h-16 object-cover rounded">`
                : `<div class="w-12 h-16 bg-gray-200 flex items-center justify-center rounded">🎬</div>`;

            let url = '#';
            if (type === 'films') url = BASE_URL + 'films/' + (i.id ?? '');
            if (type === 'articles') url = BASE_URL + 'articles/' + (i.id ?? '');
            if (type === 'lieux') url = BASE_URL + 'lieux/' + (i.id ?? '');

            const sub = i.release_date ? `<div class="text-xs text-gray-500">${escapeHtml(i.release_date)}</div>` : '';

            return `<a href="${url}" class="flex gap-3 items-center">` +
                `<div class="flex-shrink-0">${imgHtml}</div><div class="text-sm">${escapeHtml(i.label)}${sub}</div></a>`;
        }).join('');

        container.classList.remove('hidden');
    };

    const fetchSuggestions = async (input) => {
        const query = (input.value || '').trim();
        if (query.length < 2) {
            updateDatalist(input, []);
            return;
        }

        const endpoint = input.dataset.autocompleteEndpoint;
        if (!endpoint) return;

        const data = await apiFetch(endpoint, 'POST', { query });
        const items = data && data.results ? data.results : [];
        updateDatalist(input, items);
        // si l'input declare un container de suggestions, on renderise HTML (avec images si fournies)
        if (input.dataset.suggestContainer) {
            renderSuggestions(input, items);
        }
    };

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            window.clearTimeout(timers.get(input));
            const t = window.setTimeout(() => fetchSuggestions(input), 250);
            timers.set(input, t);
        });
    });

    // Masque les containers de suggestions quand on clique à l'extérieur
    document.addEventListener('click', (e) => {
        inputs.forEach(input => {
            const sel = input.dataset.suggestContainer;
            if (!sel) return;
            const container = document.querySelector(sel);
            if (!container) return;
            if (!container.contains(e.target) && !input.contains(e.target)) {
                container.classList.add('hidden');
            }
        });
    });
};

initSearchAutocomplete();

// Like/Favori sur les articles
document.querySelectorAll('[data-like]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const articleId = btn.dataset.like;
        const data = await apiFetch(BASE_URL + 'articles/' + articleId + '/like');
        const icon = btn.querySelector('.like-count');
        const likeIcon = btn.querySelector('.like-icon');
        if (icon) icon.textContent = parseInt(icon.textContent) + (data.liked ? 1 : -1);
        btn.classList.toggle('active', data.liked);
        btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
        if (likeIcon) likeIcon.textContent = data.liked ? '♥' : '♡';
    });
});

document.querySelectorAll('[data-favori]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const articleId = btn.dataset.favori;
        const data = await apiFetch(BASE_URL + 'articles/' + articleId + '/favori');
        const favoriteIcon = btn.querySelector('.favorite-icon');
        btn.classList.toggle('active', data.added);
        btn.setAttribute('aria-pressed', data.added ? 'true' : 'false');
        if (favoriteIcon) favoriteIcon.textContent = data.added ? '★' : '☆';
    });
});

document.querySelectorAll('[data-comment-like]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const commentId = btn.dataset.commentLike;
        const data = await apiFetch(BASE_URL + 'commentaires/' + commentId + '/like');
        const countEl = btn.querySelector('.comment-like-count');
        const iconEl = btn.querySelector('.comment-like-icon');
        if (countEl) {
            const current = parseInt(countEl.textContent, 10) || 0;
            countEl.textContent = current + (data.liked ? 1 : -1);
        }
        btn.classList.toggle('active', data.liked);
        btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
        if (iconEl) iconEl.textContent = data.liked ? '♥' : '♡';
    });
});

// Newsletter
document.getElementById('form-newsletter')?.addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const alert = document.getElementById('newsletter-alert');

    // Envoi du FormData complet (inclut CSRF)
    const formData = new FormData(form);
    const data = await apiFetch(BASE_URL + 'newsletter', 'POST', formData);

    alert.textContent = data.message;
    alert.className = 'alert alert-' + (data.success ? 'success' : 'error');
    alert.classList.remove('hidden');
    if (data.success) {
        form.reset();
    }
});