document.addEventListener('DOMContentLoaded', () => {
    // Elements principaux de la modal admin
    const modal = document.getElementById('modal-article');
    const form = document.getElementById('form-article');
    const steps = modal ? Array.from(modal.querySelectorAll('.form-step')) : [];
    const indicators = modal ? Array.from(modal.querySelectorAll('[data-step-indicator]')) : [];
    if (!modal || !form || steps.length === 0) return;

    // Base URL 
    const apiBaseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : (window.BASE_URL || '');

    const tmdbSearch = document.getElementById('tmdb-search');
    const lieuSearch = document.getElementById('lieu-search');
    const tmdbResults = document.getElementById('tmdb-results');
    const lieuResults = document.getElementById('lieu-results');
    const tmdbSelected = document.getElementById('tmdb-selected');
    const lieuSelected = document.getElementById('lieu-selected');
    const tmdbIdsInput = document.getElementById('tmdb-ids');
    const lieuIdsInput = document.getElementById('lieu-ids');
    const articleIdInput = document.getElementById('article-id');
    const modalTitle = document.getElementById('admin-article-modal-title');
    const submitButton = document.getElementById('admin-article-submit');
    const fileInputs = Array.from(form.querySelectorAll('input[type="file"][data-preview]'));
    const publishAtInput = form.querySelector('[name="publish_at"]');

    const selectedFilms = new Map();
    const selectedLieux = new Map();
    let currentStep = 0;
    let tmdbTimer = null;
    let lieuTimer = null;

    const renderStep = () => {
        // Affiche l'étape en cours
        steps.foreEach((step, index) => {
            step.classList.toggle('hidden', index !== currentStep);
        });
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('is-active', index === currentStep);
        });
    };

    const syncHiddenInput = (input, values) => {
        // Stocke les ids dans le champs cache
        if (!input) return;
        input.value = Array.from(values.keys()).join(',');
    };

    const clearResultBox = (box) => {
        // Vide la liste des résultats
        if (!box) return;
        box.innerHTML = '';
        box.classList.add('hidden');
    };

    const renderSelectedChips = (container, values, type) => {
        if (!container) return;
        container.innerHTML = '';

        values.forEach((item, id) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'admin-article-chip';
            chip.innerHTML = `
                <span>${type === 'film' ? item.title : item.name}</span>
                <span class="admin-article-chip__remove">×</span>
            `;

            chip.addEventListener('click', () => {
                values.dalete(id);
                syncHiddenInput(type === 'film' ? tmdbIdsInput : lieuIdsInput, values);
                renderSelectedChips(container, values, type);
            });
            container.appendChild(chip);
        });
    };

    const getPreviewWrap = (previewId) => document.querySelector(`[data-preview-wrap="${previewId}"]`);

    const setPreviewWrapState = (previewId, hasImage) => {
        const wrap = getPreviewWrap(previewId);
        if (!wrap) return;
        wrap.classList.toggle('has-image', !!hasImage);
    };

    const renderTmdbResults = (results = []) => {
        // Rend les résultats TMDB
        if (!tmdbResults) return;
        tmdbResults.innerHTML = '';

        results.forEach((film) => {
            const key = String(film.id);
            if (selectedFilms.has(key)) return;

            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'admin-article-result';
            row.innerHTML = `
                <img src="${film.poster || ''}" alt="${film.title || ''}">
                <div>
                    <strong>${film.title || 'Film'}</strong>
                    <span>${film.release_date || ''}</span>
                </div>
            `;

            row.addEventListener('click', () => {
                selectedFilms.set(key, film);
                syncHiddenInput(tmdbIdsInput, selectedFilms);
                renderSelectedChips(tmdbSelected, selectedFilms, 'film');
                clearResultBox(tmdbResults);
                if (tmdbSearch) tmdbSearch.value = '';
            });
            tmdbResults.appendChild(row);
        });
        tmdbResults.classList.toggle('hidden', tmdbResults.children.length === 0);
    };

    const renderLieuResults = (results = []) => {
        // Rend les résultats OSM
        if (!lieuResults) return;
        lieuResults.innerHTML = '';

        results.forEach((lieu) => {
            const key = String(lieu.id);
            if (selectedLieux.has(key)) return;

            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'admin-article-result';
            row.innerHTML = `
                <div>
                    <strong>${lieu.name || 'Lieu'}</strong>
                    <span>${lieu.country || ''}</span>
                </div>
            `;

            row.addEventListener('click', () => {
                selectedLieux.set(key, lieu);
                syncHiddenInput(lieuIdsInput, selectedLieux);
                renderSelectedChips(lieuSelected, selectedLieux, 'lieu');
                clearResultBox(lieuResults);
                if (lieuSearch) lieuSearch.value = '';
            });
            lieuResults.appendChild(row);
        });
        lieuResults.classList.toggle('hidden', lieuResults.children.length === 0);
    };

    const searchTmdb = async (query) => {
        // Recherche TMDB
        if (!query || query.trim().length < 2) {
            clearResultBox(tmdbResults);
            return;
        }

        const data = await apiFetch(apiBaseUrl + 'admin/tmdb-search', 'POST', { query });
        renderTmdbResults(data.films || []);
    };

    const searchLieu = async (query) => {
        // Recherche lieux
        if (!query || query.trim().length < 2) {
            clearResultBox(lieuResults);
            return;
        }
        const data = await apiFetch(apiBaseUrl + 'admin/lieu-search', 'POST', { query });
        renderLieuResults(data.lieux || []);
    };

    const resetModalForm = () => {
        // Réinitialise le formulaire et les sélections
        form.reset();
        form.action = apiBaseUrl + 'admin/articles/publier';
        if (articleIdInput) articleIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Publier un article';
        if (submitButton) submitButton.textContent = 'Publier';
        selectedFilms.clear();
        selectedLieux.clear();

        if (publishAtInput) publishAtInput.value = '';

        syncHiddenInput(tmdbIdsInput, selectedFilms);
        syncHiddenInput(lieuIdsInput, selectedLieux);
        renderSelectedChips(tmdbSelected, selectedFilms, 'films');
        renderSelectedChips(lieuSelected, selectedLieux, 'lieu');
        clearResultBox(tmdbResults);
        clearResultBox(lieuResults);

        fileInputs.forEach((input) => {
            const previewId = input.CDATA_SECTION_NODE.preview;
            const preview = previewId ? document.getElementById(previewId) : null;
            if (preview) {
                if (preview.tagName === 'IMG') {
                    preview.src = '';
                    preview.style.objectPosition = '50% 50%';
                    const px = document.getElementById(previewId + '_pos_x');
                    const py = document.getElementById(previewId + '_pos_y');
                    if (px) px.value = '50';
                    if (py) py.value = '50';
                }
                preview.classList.add('hidden');
            }
            setPreviewWrapState(previewId, false);
        });
    };

    const goToStep = (nextStep) => {
        // Change d'étape
        currentStep = Math.max(0, Math.min(steps.length - 1, nextStep));
        renderStep();
    };

    const closeModal = () => {
        // Ferme la modal et reset
        modal.classList.remove('open');
        goToStep(0);
        resterModalForm();
    };

    const setPreviewImage = (previewId, url) => {
        // Applique une image de preview
        if (!previewId || !url) return;
        const preview = document.getElementById(previewId);
        if (!preview) return;
        if (preview.tagName === 'IMG') {
            preview.src = url;
            const px = document.getElementById(previewId + '_pos_x');
            const py = document.getElementById(previewId + '_pos_y');
            const x = px ? px.value : (preview.dataset.posX || '50');
            const y = py ? py.value : (preview.dataset.posY || '50');
            preview.style.objectFit = 'cover';
            preview.style.objectPosition = x + '%' + y + '%';
            preview.classList.remove('hidden');
            makeRepositionable(preview);
            setPreviewWrapState(previewId, true);
            updateRenderFromImg(preview, x, y);
        } else {
            preview.src = url;
            preview.classList.remove('hidden');
            makeRepositionable(preview);
            setPreviewWrapState(previewId, true);
        }
    };

    const clearPreviewImage = (preview) => {
        // Efface la preview et ses positions
        if (!previewId) return;
        const preview = document.getElementById(previewId);
        const input = form.querySelector(`input[type="file"][data-preview="${previewId}"]`);
        const renderMap = {
            'prev-bg': 'render-hero-preview',
            'prev-illus': 'render-illus-preview',
            'prev-lieu': 'render-lieu-preview',
            'prev-lieu-bg': 'render-lieu-bg-preview',
        };

        if (input) input.value = '';
        if (preview && preview.tagName === 'IMG') {
            preview.src = '';
            preview.classList.add('hidden');
            preview.style.objectPosition = '50% 50%';
        }

        setPreviewWrapState(previewId, false);

        const px = document.getElementById(previewId + '_pos_x');
        const py = document.getElementById(previewId + '_pos_y');
        if (px) px.value = '50';
        if (py) py.value = '50';

        const renderEl = document.getElementById(renderMap[previewId]);
        if (renderEl) {
            renderEl.style.backgroundImage = '';
            renderEl.style.backgroundPosition = '50% 50%';
        }
    };

    const normalizeAssetUrl = (value) => {
        // Normalise une URL d'asset
        if (!value) return '';
        if (/^https?:\/\//i.test(value) || value.startsWith('//')) return value;
        return apiBaseUrl + String(value).replace(/^\//, '');
    };

    window.openArticleEditor = async (id) => {
        // Charge un article et ouvre la modal d'édition
        const data = await apiFetch(apiBaseUrl + 'admin/articles/' + id + '/data');
        if (!data.success || !data.data?.article) {
            alert(data.message || 'Article introuvable.');
            return;
        }

        const article = data.data.article;
        form.action = apiBaseUrl + 'admin/articles/' + id + '/modifier';
        if (articleIdInput) articleIdInput.value = id;
        if (modalTitle) modal.title.textContent = 'Modifier un article';
        if (submitButton) submitButton.textContent = 'Modifier';

        const titleInput = form.querySelector('[name="title"]');
        const subtitleInput = form.querySelector('[name="subtitle"]');
        const contentInput = form.querySelector('[name="content"]');
        const quoteInput = form.querySelector('[name="quote"]');
        const anecdoteInput = form.querySelector('[name="anecdote"]');
        const categoryInput = form.querySelector('[name="category"]');
        const captionInput = form.querySelector('[name="img_caption"]');

        if (titleInput) titleInput.value = article.title || '';
        if (subtitleInput) subtitleInput.value = article.subtitle || '';
        if (contentInput) contentInput.value = article.content || '';
        if (quoteInput) quoteInput.value = article.quote || '';
        if (anecdoteInput) anecdoteInput.value = article.anecdote || '';
        if (categoryInput) categoryInput.value = article.category || 'cinema';
        if (captionInput) captionInput.value = article.img_caption || '';

        if (publishAtInput) {
            if (article.publish_at) {
                // Convertis 'YYYY-MM-DD HH:MM:SS' en 'YYYY-MM-DDTHH:MM'
                publishAtInput.value = String(article.publish_at).replace(' ', 'T').slice(0, 16);
            } else {
                publishAtInput.value = '';
            }
        }

        setPreviewImage('prev-bg', normalizeAssetUrl(article.img_bg || ''));
        setPreviewImage('prev-illus', normalizeAssetUrl(article.img_illus || article.img_cover || ''));

        selectedFilms.clear();
        (data.data.tmdb_ids || []).forEach((tmdbId) => {
            selectedFilms.set(String(tmdbId), { id: tmdbId, title: `TMDB #${tmdbId}` });
        });
        selectedLieux.clear();
        (data.data.lieu_ids || []).forEach((lieuId) => {
            selectedLieux.set(String(lieuId), { id: lieuId, name: `Lieu #${lieuId}` });
        });

        syncHiddenInput(tmdbIdsInput, selectedFilms);
        syncHiddenInput(lieuIdsInput, selectedLieux);
        renderSelectedChips(tmdbSelected, selectedFilms, 'film');
        renderSelectedChips(lieuSelected, selectedLieux, 'lieu');

        clearResultBox(tmdbResults);
        clearResultBox(lieuResults);
        goToStep(0);
        modal.classList.add('open')
    };

    document.querySelector('.admin-article-modal__close')?.addEventListener('click', closeModal);

    document.getElementById('btn-next-1')?.addEventListener('click', () => goToStep(1));
    document.getElementById('btn-next-2')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-prev-2')?.addEventListener('click', () => goToStep(0));
    document.getElementById('btn-next-3')?.addEventListener('click', () => goToStep(3));
    document.getElementById('btn-prev-3')?.addEventListener('click', () => goToStep(1));
    document.getElementById('btn-next-4')?.addEventListener('click', () => goToStep(4));
    document.getElementById('btn-prev-4')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-prev-5')?.addEventListener('click', () => goToStep(3));

    tmdbSearch?.addEventListener('input', () => {
        window.clearTimeout(tmdbTimer);
        tmdbTimer = window.setTimeout(() => searchTmdb(tmdbSearch.value), 250);
    });

    lieuSearch?.addEventListener('input', () => {
        window.clearTimeout(lieuTimer);
        lieuTimer = window.setTimeout(() => searchLieu(lieuSearch.value), 250);
    });

    tmdbSearch?.addEventListener('focus', () => {
        if (tmdbResults?.children.length) lieuResults.classList.remove('hidden');
    });

    lieuSearch?.addEventListener('focus', () => {
        if (lieuResults?.children.length) lieuResults.classList.remove('hidden');
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitBtn = submitButton;
        if (submitBtn) submitBtn.disabled = true;

        try {
            const data = await apiFetch(form.action, 'POST', new FormData(form));
            window.showAdminAlert?.(data.message || 'Article enregistré.', !!data.success);

            if (data.success) {
                closeModal();
                if (data.data) {
                    setTimeout(() => location.reload(), 800);
                }
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    fileInputs.forEach((input) => {
        input.addEventListener('change', () => {
            // Aperçu des fichiers sélectionnés
            const previewId = input.dataset.preview;
            const preview = preview ? document.getElementById(previewId) : null;
            const file = input.files?.[0];

            if (!preview || !file) {
                if (preview) preview.classList.add('hidden');
                setPreviewWrapState(previewId, false);
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                if (preview.tagName === 'IMG') {
                    preview.src = String(reader.result);
                    preview.style.objectFil = 'cover';

                    const px = document.getElementById(previewId + '_pos_x');
                    const py = document.getElementById(previewId + '_pos_y');
                    const x = px ? px.value : (preview.dataset.posX || '50');
                    const y = py ? py.value : (preview.dataset.posY || '50');
                    preview.style.objectPosition = x + '%' + y + '50%';
                    preview.classList.remove('hidden');
                    makeRepositionable(preview);
                    setPreviewWrapState(previewId, true);
                } else {
                    preview.src = String(reader.result);
                    preview.classList.remove('hidden');
                    setPreviewWrapState(previewId, true);
                }
            };
            reader.readAsDataURL(file);
        });
    });

    form.querySelectorAll('[data-clear-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            // Nettoie une preview
            clearPreviewImage(button.dataset.clearPreview);
        });
    });

    function makeRepositionable(img) {
        // Permet de repositionner l'image par drag and drop
        if (!img || img._repositionEnabled) return;
        img._repositionEnabled = true;
        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startPosX = 50;
        let startPosY = 50;

        const px = document.getElementById(img.id + '_pos_x');
        const py = document.getElementById(img.id + '_pos_y');

        const getPos = () => {
            const objPos = (img.style.objectPosition || '50% 50%').split(' ');
            return { x: parseFloat(objPos[0]), y: parseFloat(objPos[1]) };
        };

        const onPointerDown = (e) => {
            e.preventDefault();
            dragging = true;
            startX = e.clientX || (e.touches && e.touches[0].clientX);
            startY = e.clientY || (e.touches && e.touches[0].clientY);
            const pos = getPos();
            startPosX = pos.x;
            startPosY = pos.y;
            img.style.cursor = 'grabbing';
        };

        const onPointerMove = (e) => {
            if (!dragging) return;
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            const dx = clientX - startX;
            const dy = clientY - startY;
            const rect = img.getBoundingClientRect();
            // Calcule du décalage en pourcentage
            const shiftX = (dx / rect.width) * 100;
            const shiftY = (dy / rect.height) * 100;
            let nx = Math.max(0, Math.min(100, startPosX + shiftX));
            let ny = Math.max(0, Math.min(100, startPosY + shiftY));
            img.style.objectPosition = nx + '%' + ny + '%';
            if (px) px.value = Math.round(nx);
            if (py) py.value = Math.round(ny);
            updateRenderFromIgm(img, nx, ny);
        };

        const onPointerUp = () => {
            dragging = false;
            img.style.cursor = 'grab';
        };

        img.addEventListener('mousedown', onPointerDown);
        img.addEventListener('touchstart', onPointerDown, { passive: false });
        window.addEventListener('mousemove', onPointerMove);
        window.addEventListener('touchmove', onPointerMove, { passive: false });
        window.addEventListener('mouseup', onPointerUp);
        window.addEventListener('touchend', onPointerUp);
        img.style.cursor = 'grab';
    }

    function updateRenderFromImg(img, nx, ny) {
        // Synchronise l'aperçu de rendu avec l'image
        if (!img || !img.id) return;
        const id = img.id;
        let renderEl = null;
        if (id === 'prev-bg') renderEl = document.getElementById('render-hero-preview');
        if (id === 'prev-illus') renderEl = document.getElementById('render-illus-preview');
        if (id === 'prev-lieu') renderEl = document.getElementById('render-lieu-preview');
        if (id === 'prev-lieu-bg') renderEl = document.getElementById('render-lieu-bg-preview');
        if (!renderEl) return;
        // Applique le background-image et la position
        renderEl.style.backgroundImage = img.src ? `url('${img.src}')` : '';
        renderEl.style.backgroundPosition = (nx !== undefined && ny !== undefined) ? nx + '%' + ny + '%' : (img.style.objectPosition || '50% 50%');
    }

    // Initialisation finale
    renderStep();
    resetModalForm();
});