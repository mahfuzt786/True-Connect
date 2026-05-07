<h2>Import Products from CSV</h2>
<div class="card p-4">
<form method="POST" action="/products/import" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <p>Upload a CSV file with the following columns:</p>
    <code>name, sku, price, compare_price, quantity, description, category, status</code>
    <div class="my-3"><input type="file" name="csv_file" accept=".csv" class="form-control" required></div>
    <button class="btn btn-primary">Upload & Import</button>
    <a href="/products" class="btn btn-link">Cancel</a>
</form>
</div>
