<?php $page_bg = asset_url('frontend/assets/img/bg/contact.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-2xl mx-auto px-4 sm:px-6 pb-20">
    <img src="<?= asset_url('frontend/assets/img/icons/globe.png') ?>" alt="globe" class="globe-icon no-sepia">
    <h1 class="about-page__title section-title mt-3 text-2xl text-[var(--gold)] md:text-3xl">
        Contactez-nous
    </h1>

    <div class="bloc-cuir p-5 mb-6">
        <p class="text-sm leading-relaxed opacity-85 text-center">
            Vous avez une question ou une suggestion ?<br>
            Contactez-nous, nous donnerons suite au plus vite.
        </p>
    </div>
    <div id="contact-alert" class="hidden mb-4 rounded-lg px-4 py-2 text-sm"></div>
    <div class="bloc-cuir p-5 md:p-6">
        <form id="form-contact" class="flex flex-col gap-4">
            <?= CsrfMiddleware::field() ?>
            <?php foreach (
                [
                    ['text',  'pseudo',  'Pseudo *'],
                    ['email', 'email',   'Email *'],
                    ['text',  'subject', 'Objet *'],
                ] as [$t, $n, $l]
            ): ?>
                <div>
                    <label class="block text-xs opacity-70 mb-1" for="<?= $n ?>"><?= $l ?></label>
                    <input type="<?= $t ?>" name="<?= $n ?>" class="form-input" required>
                </div>
            <?php endforeach; ?>
            <div>
                <label class="block text-xs opacity-70 mb-1">Message *</label>
                <textarea name="message" rows="5" class="form-input resize-none" required></textarea>
            </div>
            <button type="submit" class="btn w-full justify-center">Envoyer</button>
        </form>
    </div>
</main>