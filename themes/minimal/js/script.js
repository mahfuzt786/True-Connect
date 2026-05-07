function addToCart(productId, qty=1, variantId=null) {
    const fd=new FormData(); fd.append('product_id',productId); fd.append('quantity',qty); if(variantId)fd.append('variant_id',variantId);
    fd.append('_csrf_token',document.querySelector('meta[name=csrf-token]').content);
    return fetch((window.STORE_BASE || '')+'/cart/add',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(d=>{if(d.success)showToast('Added to cart');else showToast(d.error,'danger');return d;});
}
function toggleWishlist(id){const fd=new FormData();fd.append('product_id',id);fd.append('_csrf_token',document.querySelector('meta[name=csrf-token]').content);return fetch((window.STORE_BASE || '')+'/wishlist/toggle',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json());}
function showToast(msg,type='success'){const t=document.createElement('div');t.className='position-fixed bottom-0 end-0 m-3 alert alert-'+type;t.style.zIndex=9999;t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),2500);}
