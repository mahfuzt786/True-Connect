<h2>Revenue Analytics</h2>
<form class="mb-3"><select name="period" class="form-select w-auto" onchange="this.form.submit()">
<?php foreach ([7,30,90,365] as $p): ?><option value="<?= $p ?>" <?= $period==$p?'selected':'' ?>>Last <?= $p ?> days</option><?php endforeach; ?>
</select></form>
<div class="card"><div class="card-body"><canvas id="chart" height="80"></canvas></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{const c=<?=json_encode($data['current'])?>,p=<?=json_encode($data['previous'])?>;new Chart(document.getElementById('chart'),{type:'line',data:{labels:c.map(x=>x.d),datasets:[{label:'Current',data:c.map(x=>parseFloat(x.revenue)),borderColor:'#0d6efd'},{label:'Previous',data:p.map(x=>parseFloat(x.revenue)),borderColor:'#ccc',borderDash:[5,5]}]}});});</script>
