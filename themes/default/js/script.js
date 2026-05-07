function addToCart(productId, qty = 1, variantId = null) {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('quantity', qty);
    if (variantId) fd.append('variant_id', variantId);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    return fetch((window.STORE_BASE || '') + '/cart/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(d => {
            if (d.success) showToast('Added to cart!');
            else showToast(d.error || 'Error', 'danger');
            return d;
        });
}
function toggleWishlist(productId) {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    return fetch((window.STORE_BASE || '') + '/wishlist/toggle', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(d => {
            if (d.error) { window.location = '/login'; return; }
            showToast(d.in_wishlist ? 'Added to wishlist' : 'Removed from wishlist');
            return d;
        });
}
function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = 'position-fixed top-0 end-0 m-3 alert alert-' + type + ' shadow';
    t.style.zIndex = 9999;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
