// Navbar - fond etoile + comportement au scroll
(function () {
    const canvas = document.getElementById('navbar-stars');
    const navbar = document.getElementById('main-navbar');

    if (!navbar) return;

    let stickyPoint = 0;

    function refreshStickyPoint() {
        // Calcule le point d'accroche
        stickyPoint = navbar.offsetTop;
    }

    function handleScroll() {
        // Ajoute/retire la classe selon le scroll
        document.body.classList.toggle('navbar-scrolled', window.scrollY >= stickyPoint);
    }

    if (canvas) {
        const ctx = canvas.getContext('2d');
        const stars = [];
        const COUNT = 100;
        let resizeObserver = null;
        let canvasWidth = 0;
        let canvasHeight = 0;

        function resize() {
            // Ajuste le canvas au pixel ratio
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight;
            const dpr = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = Math.round(width * dpr);
            canvas.height = Math.round(height * dpr);
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            canvasWidth = width;
            canvasHeight = height;
        }

        function buildStars() {
            // Genere les etoiles
            stars.length = 0;

            for (let i = 0; i < COUNT; i++) {
                stars.push({
                    x: Math.random() * canvasWidth,
                    y: Math.random() * canvasHeight,
                    r: Math.random() * 0.8 + 0.35,
                    alpha: Math.random() * 0.25 + 0.4,
                    vx: (Math.random() - 0.5) * 0.15,
                    vy: (Math.random() - 0.5) * 0.15,
                });
            }
        }

        function draw() {
            // Dessine les etoiles
            const dpr = Math.max(window.devicePixelRatio || 1, 1);

            // Reinitialiser la transformation pour effacer correctement
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Remettre la transformation pour le dessin
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.globalCompositeOperation = 'source-over';

            stars.forEach(star => {
                ctx.beginPath();
                ctx.arc(star.x, star.y, star.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(255, 224, 182, ${star.alpha})`;
                ctx.fill();
            });
        }

        function animate() {
            // Anime le fond etoile
            stars.forEach(star => {
                star.x += star.vx;
                star.y += star.vy;

                // Wrap-around avec inversion de vitesse
                if (star.x < -10) {
                    star.x = canvasWidth + 10;
                    star.vx = Math.abs(star.vx);  // Forcer direction droite
                }
                if (star.x > canvasWidth + 10) {
                    star.x = -10;
                    star.vx = -Math.abs(star.vx);  // Forcer direction gauche
                }
                if (star.y < -10) {
                    star.y = canvasHeight + 10;
                    star.vy = Math.abs(star.vy);  // Forcer direction bas
                }
                if (star.y > canvasHeight + 10) {
                    star.y = -10;
                    star.vy = -Math.abs(star.vy);  // Forcer direction haut
                }
            });

            draw();
            requestAnimationFrame(animate);
        }

        function syncCanvas() {
            // Recalcule le rendu
            resize();
            buildStars();
            draw();
        }

        syncCanvas();

        if (window.ResizeObserver) {
            resizeObserver = new ResizeObserver(() => {
                syncCanvas();
            });
            resizeObserver.observe(navbar);
        }

        // Lance l'animation
        animate();

        window.addEventListener('resize', () => {
            syncCanvas();
            refreshStickyPoint();
            handleScroll();
        });
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('load', () => {
        refreshStickyPoint();
        handleScroll();
    });

    refreshStickyPoint();
    handleScroll();
})();

// Menu hamburger (mobile)
(function () {
    const burger = document.getElementById('nav-burger');
    const menu = document.getElementById('nav-menu-mobile');
    if (!burger || !menu) return;

    burger.addEventListener('click', () => {
        // Ouvre/ferme le menu
        menu.classList.toggle('hidden');
        burger.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));

        window.requestAnimationFrame(() => {
            // Force un resize pour recalculer le canvas
            window.dispatchEvent(new Event('resize'));
        });
    });
})();