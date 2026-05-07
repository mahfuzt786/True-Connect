// Reuse default theme JS pattern
function addToCart(productId, qty = 1, variantId = null) {
    const fd = new FormData();
    fd.append('product_id', productId); fd.append('quantity', qty);
    if (variantId) fd.append('variant_id', variantId);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    return fetch((window.STORE_BASE || '') + '/cart/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(d => { if (d.success) showToast('Added!'); else showToast(d.error || 'Error', 'danger'); return d; });
}
function toggleWishlist(productId) {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('_csrf_token', document.querySelector('meta[name=csrf-token]').content);
    return fetch((window.STORE_BASE || '') + '/wishlist/toggle', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(d => { if (d.error) { window.location='/login'; return; } showToast(d.in_wishlist ? '♥ Saved' : 'Removed'); return d; });
}
function showToast(msg, type='success') {
    const t=document.createElement('div'); t.className='position-fixed top-0 end-0 m-3 alert alert-'+type+' shadow-lg'; t.style.zIndex=9999; t.textContent=msg;
    document.body.appendChild(t); setTimeout(()=>t.remove(),3000);
}
