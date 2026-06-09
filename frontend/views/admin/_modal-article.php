<div id="modal-article" class="modal-overlay admin-article-modal">
    <div class="modal admin-article-modal__panel w-full">
        <div class="admin-article-modal__topbar">
            <button
                type="button"
                class="admin-article-modal__close"
                aria-label="Fermer la fenêtre"
                onclick="document.getElementById('modal-article').classList.remove('open')">
                ×
            </button>
        </div>

        <div class="admin-article-modal__header">
            <p class="admin-article-modal__eyebrow">Admin - Article modal</p>
            <h3 id="admin-article-modal-title" class="admin-article-modal__title">Publier un article</h3>
        </div>

        <div class="admin-article-modal__steps" aria-label="Progression de création d'article">
            <span class="step-indicator is-active" data-step-indicator="0">Étape 1/5 : Contenu principal</span>
            <span class="step-indicator" data-step-indicator="1">Étape 2/5 : Contenu de droite</span>
            <span class="step-indicator" data-step-indicator="2">Étape 3/5 : Infos du lieu</span>
            <span class="step-indicator" data-step-indicator="3">Étape 4/5 : Images slider</span>
            <span class="step-indicator" data-step-indicator="4">Étape 5/5 : Images</span>
        </div>

        <form id="form-article" method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>admin/articles/publier" class="admin-article-form">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" id="article-id" name="article_id" value="">
            <input type="hidden" id="tmdb-ids" name="tmdb_ids">
            <input type="hidden" id="lieu-ids" name="lieu_ids">

            <section class="form-step admin-article-step">
                <div class="admin-article-step__label">Étape 1/5: Contenu principal</div>
                <div class="admin-article-fields">
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Titre</label>
                        <input type="text" id="input-title" name="title" maxlength="200" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Sous-titre</label>
                        <input type="text" name="subtitle" maxlength="255" class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Contenu</label>
                        <textarea id="input-content" name="content" rows="5" required class="form-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Citation</label>
                        <textarea name="quote" rows="4" class="form-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Anecdote</label>
                        <textarea name="anecdote" rows="4" class="form-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Catégorie</label>
                        <select name="category" class="form-input">
                            <option value="cinema">Cinéma</option>
                            <option value="voyage">Voyage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Programmer la publication (optionnel)</label>
                        <input type="datetime-local" name="publish_at" class="form-input">
                        <p class="text-xs opacity-60 mt-1">Laissez vide pour publier immédiatement. Si une date future est choisie, l'article sera mis en attente puis publié automatiquement.</p>
                    </div>
                </div>
                <button type="button" id="btn-next-1" class="btn admin-article-step__action">Suivant</button>
            </section>

            <section class="form-step admin-article-step hidden">
                <div class="admin-article-step__label">Étape 2/5: Contenu de droite</div>
                <div class="admin-article-fields">
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Films tournés ici</label>
                        <input type="text" id="tmdb-search" placeholder="Chercher un film" class="form-input" data-suggest-container="#tmdb-results" data-autocomplete-type="films">
                        <div id="tmdb-results" class="hidden admin-article-results suggestions-cuir"></div>
                        <div id="tmdb-selected" class="admin-article-selected"></div>
                    </div>

                    <div>
                        <label class="block text-xs opacity-70 mb-1">Lieux de tournage associés</label>
                        <input type="text" id="lieu-search" placeholder="Chercher un lieu" class="form-input" data-suggest-container="#lieu-results" data-autocomplete-type="lieux">
                        <div id="lieu-results" class="hidden admin-article-results suggestions-cuir"></div>
                        <div id="lieu-selected" class="admin-article-selected"></div>
                    </div>
                </div>

                <div class="admin-article-step__nav">
                    <button type="button" id="btn-prev-2" class="btn">Précédent</button>
                    <button type="button" id="btn-next-2" class="btn">Suivant</button>
                </div>
            </section>

            <section class="form-step admin-article-step hidden">
                <div class="admin-article-step__label">Étape 3/5: Infos du lieu</div>
                <div class="admin-article-fields">
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Description du lieu</label>
                        <textarea name="lieu_description" rows="4" class="form-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Conseils de l'auteur</label>
                        <textarea name="lieu_tips" rows="4" class="form-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Suggestions de l'auteur</label>
                        <textarea name="lieu_suggestions" rows="4" class="form-input resize-none"></textarea>
                    </div>
                </div>

                <div class="admin-article-step__nav">
                    <button type="button" id="btn-prev-3" class="btn">Précédent</button>
                    <button type="button" id="btn-next-3" class="btn">Suivant</button>
                </div>
            </section>

            <section class="form-step admin-article-step hidden">
                <div class="admin-article-step__label">Étape 4/5: Images slider</div>
                <div class="admin-article-fields">
                    <p class="text-xs opacity-60">Ajoutez jusqu'a 5 images liees a l'article. Chaque image peut avoir un titre et un petit texte descriptif.</p>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_1.2fr] gap-3 items-start">
                            <div>
                                <label class="block text-xs opacity-70 mb-1">Image slider #<?= $i ?></label>
                                <input type="file" name="slider_img_<?= $i ?>" accept="image/*" class="form-input text-xs">
                            </div>
                            <div class="flex flex-col gap-3">
                                <div>
                                    <label class="block text-xs opacity-70 mb-1">Titre #<?= $i ?></label>
                                    <input type="text" name="slider_title_<?= $i ?>" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs opacity-70 mb-1">Texte descriptif #<?= $i ?></label>
                                    <textarea name="slider_text_<?= $i ?>" rows="3" class="form-input resize-none"></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="admin-article-step__nav">
                    <button type="button" id="btn-prev-4" class="btn">Précédent</button>
                    <button type="button" id="btn-next-4" class="btn">Suivant</button>
                </div>
            </section>

            <section class="form-step admin-article-step hidden">
                <div class="admin-article-step__label">Étape 5/5: Images</div>
                <div class="admin-article-fields">
                    <?php foreach (
                        [
                            ['img_bg', 'prev-bg', 'Image Hero'],
                            ['img_illus', 'prev-illus', "Image illustration d'article"],
                        ] as [$name, $prevId, $label]
                    ): ?>
                        <div>
                            <label class="block text-xs opacity-70 mb-1"><?= $label ?></label>
                            <input type="file" name="<?= $name ?>" accept="image/*" data-preview="<?= $prevId ?>" class="form-input text-xs">
                            <div class="admin-article-preview-wrap" data-preview-wrap="<?= $prevId ?>">
                                <img id="<?= $prevId ?>" class="hidden admin-article-preview repositionable" data-pos-x="50" data-pos-y="50">
                                <input type="hidden" name="<?= $name ?>_pos_x" id="<?= $prevId ?>_pos_x" value="50">
                                <input type="hidden" name="<?= $name ?>_pos_y" id="<?= $prevId ?>_pos_y" value="50">
                                <div class="admin-article-preview-hint">Glisser pour recentrer</div>
                                <button type="button" class="admin-article-preview-delete" aria-label="Supprimer l'image" data-clear-preview="<?= $prevId ?>">×</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Image lieu de tournage (optionnel)</label>
                        <input type="file" name="lieu_img" accept="image/*" data-preview="prev-lieu" class="form-input text-xs">
                        <div class="admin-article-preview-wrap" data-preview-wrap="prev-lieu">
                            <img id="prev-lieu" class="hidden admin-article-preview repositionable" data-pos-x="50" data-pos-y="50">
                            <div class="admin-article-preview-hint">Glisser pour recentrer</div>
                            <button type="button" class="admin-article-preview-delete" aria-label="Supprimer l'image" data-clear-preview="prev-lieu">×</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs opacity-70 mb-1">Légende photo</label>
                        <input type="text" name="img_caption" class="form-input">
                    </div>
                </div>

                <div class="admin-article-render">
                    <div>
                        <div class="block text-xs opacity-70 mb-1">Aperçu rendu - Image Hero</div>
                        <div id="render-hero-preview" class="admin-article-render-hero"></div>
                    </div>
                    <div>
                        <div class="block text-xs opacity-70 mb-1">Aperçu rendu - Illustration</div>
                        <div id="render-illus-preview" class="admin-article-render-illus"></div>
                    </div>
                    <div>
                        <div class="block text-xs opacity-70 mb-1">Aperçu rendu - Image lieu</div>
                        <div id="render-lieu-preview" class="admin-article-render-lieu"></div>
                    </div>
                </div>

                <div class="admin-article-step__nav">
                    <button type="button" id="btn-prev-5" class="btn">Précédent</button>
                    <button type="submit" id="admin-article-submit" class="btn admin-article-step__submit">Publier</button>
                </div>
            </section>
        </form>
    </div>
</div>