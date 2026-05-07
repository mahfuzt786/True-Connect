<h2>Traffic</h2>
<div class="card mb-3"><div class="card-body"><canvas id="pv" height="80"></canvas></div></div>
<div class="row g-3">
<div class="col-md-6"><div class="card p-3"><h6>Top Pages</h6>
<table class="table"><thead><tr><th>Page</th><th>Views</th></tr></thead><tbody>
<?php foreach ($data['top_pages'] as $p): ?><tr><td><?= e($p['page']) ?></td><td><?= $p['views'] ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-md-6"><div class="card p-3"><h6>Devices</h6>
<canvas id="dev" height="180"></canvas></div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{
const pv=<?=json_encode($data['page_views'])?>;new Chart(document.getElementById('pv'),{type:'line',data:{labels:pv.map(x=>x.d),datasets:[{label:'Views',data:pv.map(x=>x.views),borderColor:'#0d6efd',fill:true,backgroundColor:'rgba(13,110,253,.1)'}]}});
const d=<?=json_encode($data['devices'])?>;new Chart(document.getElementById('dev'),{type:'pie',data:{labels:d.map(x=>x.device),datasets:[{data:d.map(x=>x.cnt),backgroundColor:['#0d6efd','#198754','#ffc107']}]}});
});</script>
