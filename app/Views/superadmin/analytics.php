<h2>Platform Analytics</h2>
<div class="row g-3">
<div class="col-md-4"><div class="card"><div class="card-header bg-white"><strong>Signups (30d)</strong></div><div class="card-body"><canvas id="signups"></canvas></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header bg-white"><strong>New Stores (30d)</strong></div><div class="card-body"><canvas id="stores"></canvas></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header bg-white"><strong>Orders (30d)</strong></div><div class="card-body"><canvas id="orders"></canvas></div></div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{
const s=<?=json_encode($signupsChart)?>,st=<?=json_encode($storesChart)?>,o=<?=json_encode($ordersChart)?>;
new Chart(document.getElementById('signups'),{type:'line',data:{labels:s.map(x=>x.d),datasets:[{label:'Users',data:s.map(x=>x.c),borderColor:'#0d6efd'}]}});
new Chart(document.getElementById('stores'),{type:'line',data:{labels:st.map(x=>x.d),datasets:[{label:'Stores',data:st.map(x=>x.c),borderColor:'#198754'}]}});
new Chart(document.getElementById('orders'),{type:'line',data:{labels:o.map(x=>x.d),datasets:[{label:'Orders',data:o.map(x=>x.c),borderColor:'#ffc107'}]}});
});</script>
