// Minimal site-wide autocomplete (articles, films, lieux)
(() => {
    const input = document.getElementById('site-search');
    const box = document.getElementById('search-suggestions');
    if (!input || !box) return;

    let t;
    input.addEventListener('input', () => {
        clearTimeout(t);
        const q = input.value.trim();
        if (q === '') { box.innerHTML = ''; box.classList.add('hidden'); return; }
        t = setTimeout(() => fetchSuggestions(q), 220);
    });

    function fetchSuggestions(q) {
        const fd = new FormData(); fd.append('query', q);
        fetch(BASE_URL + 'search/autocomplete', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => render(data.results || []))
            .catch(() => { box.innerHTML = ''; box.classList.add('hidden'); });
    }

    function render(items) {
        if (!items.length) { box.innerHTML = ''; box.classList.add('hidden'); return; }
        box.classList.remove('hidden');
        box.innerHTML = items.map(i => {
            const emoji = i.type === 'film' ? '🎬' : i.type === 'lieu' ? '📍' : '📝';
            return `<a href="${i.url}" class="block px-3 py-2 hover:bg-gray-100">${emoji} ${escapeHtml(i.label)}</a>`;
        }).join('');
    }

    function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !box.contains(e.target)) box.classList.add('hidden');
    });
})();
