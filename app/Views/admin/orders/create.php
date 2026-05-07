<h2>Create Order</h2>
<form method="POST" action="/orders" class="card p-4">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label>Customer (optional)</label>
            <select name="user_id" class="form-select">
                <option value="">— Guest —</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['email']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Payment Method</label>
            <select name="payment_method" class="form-select">
                <option>cod</option><option>manual</option><option>bank_transfer</option><option>stripe</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Payment Status</label>
            <select name="payment_status" class="form-select">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
            </select>
        </div>
    </div>

    <h5 class="mt-4">Items</h5>
    <table class="table" id="itemsTable">
        <thead><tr><th>Product</th><th>Qty</th><th></th></tr></thead>
        <tbody>
            <tr>
                <td><select name="items[0][product_id]" class="form-select"><option value="">Select product...</option>
                    <?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= money($p['price']) ?>)</option><?php endforeach; ?>
                </select></td>
                <td><input type="number" name="items[0][quantity]" value="1" min="1" class="form-control" style="width:100px;"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
            </tr>
        </tbody>
    </table>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItemRow()">+ Add Item</button>

    <div class="mt-3"><label>Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
    <button type="submit" class="btn btn-primary mt-3">Create Order</button>
</form>
<script>
let itemIdx = 1;
function addItemRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="items[${itemIdx}][product_id]" class="form-select">
        <option value="">Select product...</option>
        <?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= e(addslashes($p['name'])) ?> (<?= money($p['price']) ?>)</option><?php endforeach; ?>
        </select></td>
        <td><input type="number" name="items[${itemIdx}][quantity]" value="1" min="1" class="form-control" style="width:100px;"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>`;
    tbody.appendChild(tr);
    itemIdx++;
}
</script>
