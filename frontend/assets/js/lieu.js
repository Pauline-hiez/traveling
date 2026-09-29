const lieuInit = () => {
    const mapEl = document.getElementById('lieu-map');
    if (mapEl && typeof L !== 'undefined') {
        // Initialise la carte Leaflet
        const lat = Number(mapEl.dataset.lat || '');
        const lng = Number(mapEl.dataset.lng || '');
        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            const map = L.map('lieu-map').setView([lat, lng], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup(mapEl.dataset.name || 'Lieu').openPopup();
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', lieuInit);
} else {
    lieuInit();
}