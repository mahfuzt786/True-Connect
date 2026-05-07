<?php

class InvoiceService {

    public function generate(array $order, array $items, array $store): string {
        ob_start();
        $billing = json_decode($order['billing_address'], true) ?: [];
        $shipping = json_decode($order['shipping_address'], true) ?: [];
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title>Invoice <?= $order['order_number'] ?></title>
        <style>
            body { font-family: Arial, sans-serif; padding: 30px; color: #333; }
            .header { display: flex; justify-content: space-between; border-bottom: 3px solid #0d6efd; padding-bottom: 20px; }
            .company { font-size: 24px; font-weight: bold; color: #0d6efd; }
            h1 { color: #333; margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
            th { background: #f8f9fa; }
            .totals { margin-top: 20px; text-align: right; }
            .totals .total { font-size: 20px; font-weight: bold; color: #0d6efd; }
            .footer { margin-top: 50px; text-align: center; color: #999; font-size: 12px; }
            .addresses { display: flex; justify-content: space-between; margin: 20px 0; }
            .address { width: 48%; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        </style></head><body>
            <div class="header">
                <div>
                    <div class="company"><?= e($store['name']) ?></div>
                    <div><?= e($store['email'] ?? '') ?> | <?= e($store['phone'] ?? '') ?></div>
                    <div><?= nl2br(e($store['address'] ?? '')) ?></div>
                </div>
                <div style="text-align:right;">
                    <h1>INVOICE</h1>
                    <div><strong>#<?= $order['order_number'] ?></strong></div>
                    <div>Date: <?= formatDate($order['created_at']) ?></div>
                    <div>Status: <?= ucfirst($order['payment_status']) ?></div>
                </div>
            </div>
            <div class="addresses">
                <div class="address">
                    <strong>Bill To:</strong><br>
                    <?= e(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?><br>
                    <?= e($billing['email'] ?? '') ?><br>
                    <?= e($billing['address_line1'] ?? '') ?><br>
                    <?= e($billing['city'] ?? '') ?>, <?= e($billing['state'] ?? '') ?> <?= e($billing['zip_code'] ?? '') ?><br>
                    <?= e($billing['country'] ?? '') ?>
                </div>
                <div class="address">
                    <strong>Ship To:</strong><br>
                    <?= e(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? '')) ?><br>
                    <?= e($shipping['address_line1'] ?? '') ?><br>
                    <?= e($shipping['city'] ?? '') ?>, <?= e($shipping['state'] ?? '') ?> <?= e($shipping['zip_code'] ?? '') ?><br>
                    <?= e($shipping['country'] ?? '') ?>
                </div>
            </div>
            <table>
                <thead><tr><th>Item</th><th>SKU</th><th>Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?></td>
                        <td><?= e($item['sku'] ?? '-') ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td style="text-align:right;"><?= money($item['unit_price'], $order['currency']) ?></td>
                        <td style="text-align:right;"><?= money($item['total'], $order['currency']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="totals">
                <div>Subtotal: <?= money($order['subtotal'], $order['currency']) ?></div>
                <?php if ($order['discount_amount'] > 0): ?><div>Discount: -<?= money($order['discount_amount'], $order['currency']) ?></div><?php endif; ?>
                <div>Tax: <?= money($order['tax_amount'], $order['currency']) ?></div>
                <div>Shipping: <?= money($order['shipping_amount'], $order['currency']) ?></div>
                <div class="total">Total: <?= money($order['total'], $order['currency']) ?></div>
            </div>
            <div class="footer">
                Thank you for your business!<br>
                <?= e($store['name']) ?> | <?= e($store['email'] ?? '') ?>
            </div>
        </body></html>
        <?php
        $html = ob_get_clean();
        return $this->htmlToPdf($html);
    }

    public function generateSubscriptionInvoice(array $invoice, array $store): string {
        ob_start();
        ?>
        <html><body style="font-family: Arial, sans-serif; padding: 30px;">
            <h1 style="color:#0d6efd;">Invoice #<?= $invoice['invoice_number'] ?></h1>
            <p><strong>Date:</strong> <?= formatDate($invoice['created_at']) ?></p>
            <p><strong>Store:</strong> <?= e($store['name']) ?></p>
            <hr>
            <table style="width:100%;margin:20px 0;">
                <tr><td>Subscription</td><td style="text-align:right;"><?= money($invoice['amount'], $invoice['currency']) ?></td></tr>
                <tr><td><strong>Total Paid</strong></td><td style="text-align:right;"><strong><?= money($invoice['amount'], $invoice['currency']) ?></strong></td></tr>
            </table>
            <p>Status: <strong><?= ucfirst($invoice['status']) ?></strong></p>
        </body></html>
        <?php
        return $this->htmlToPdf(ob_get_clean());
    }

    private function htmlToPdf(string $html): string {
        // Use Dompdf if available, otherwise return HTML wrapped
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }
        // Fallback: return HTML so browser can print
        return $html;
    }
}
