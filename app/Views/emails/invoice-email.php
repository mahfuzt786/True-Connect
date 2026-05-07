<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#0d6efd;padding:30px;text-align:center;color:#fff;"><h2>Invoice</h2></div>
    <div style="padding:30px;">
        <p>Hi <?= e($user['name']) ?>,</p>
        <p>Please find your invoice attached for amount <strong><?= money($invoice['amount'], $invoice['currency']) ?></strong>.</p>
        <p>Invoice number: <strong><?= e($invoice['invoice_number']) ?></strong></p>
    </div>
</div></body></html>
