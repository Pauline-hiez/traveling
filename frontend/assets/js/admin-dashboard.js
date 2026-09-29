const adminDashboardInit = () => {
    // Le dashboard repose sur la logique communue de admin.js
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', adminDashboardInit);
} else {
    adminDashboardInit();
}