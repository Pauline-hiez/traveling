document.addEventListener('DOMContentLoaded', () => {
    const slides = Array.from(document.querySelectorAll('.lieu-slider .lieu-slide'));
    if (slides.length === 0) return;

    const setActive = (activeIndex) => {
        slides.forEach((slide, index) => {
            const isActive = index === activeIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            slide.clicked = isActive;
        });
    };

    const toggleSlide = (slide, index) => {
        if (slide.clicked) {
            slide.classList.remove('is-active');
            slide.setAttribute('aria-pressed', 'false');
            slide.clicked = false;
            return;
        }
        setActive(index);
    };
    slides.forEach((slide, index) => {
        slide.clicked = slide.classList.contains('is-active');
        slide.addEventListener('click', () => toggleSlide(slide, index));
    });
    if (!slides.some((slide) => slide.classList.contains('is-active'))) {
        setActive(0);
    }
});