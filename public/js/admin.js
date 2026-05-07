// Admin panel JS helpers
document.addEventListener('DOMContentLoaded', () => {
    // Confirm before destructive actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
    });
    // Auto-dismiss alerts
    setTimeout(() => document.querySelectorAll('.alert-dismissible').forEach(el => bootstrap.Alert.getOrCreateInstance(el).close()), 5000);
});

// Generic AJAX helpers
window.api = {
    csrf: () => document.querySelector('meta[name=csrf-token]')?.content,
    get: (url) => fetch(url).then(r => r.json()),
    post: (url, data) => {
        const fd = data instanceof FormData ? data : new FormData();
        if (!(data instanceof FormData)) Object.entries(data).forEach(([k,v]) => fd.append(k, v));
        fd.append('_csrf_token', window.api.csrf());
        return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
    },
    delete: (url) => fetch(url, { method: 'DELETE', headers: { 'X-CSRF-Token': window.api.csrf() } }).then(r => r.json()),
};
