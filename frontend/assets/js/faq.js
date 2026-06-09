window.toggleFaq = (i) => {
    // Ouvre/Ferme une réponse FAQ
    const answer = document.getElementById('faq-ans-' + i);
    const icon = document.getElementById('faq-icon-' + i);
    if (!answer || !icon) return;

    const open = answer.classList.toggle('hidden');
    icon.style.transform = open ? '' : 'rotate(180deg)';
};