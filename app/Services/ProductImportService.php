<?php

class ProductImportService {
    private int $storeId;

    public function __construct(int $storeId) {
        $this->storeId = $storeId;
    }

    public function importFromCsv(string $filePath): array {
        $imported = 0;
        $errors   = 0;
        $errorLog = [];

        if (($fp = fopen($filePath, 'r')) === false) {
            throw new RuntimeException('Cannot read CSV file');
        }

        $headers = fgetcsv($fp);
        $headers = array_map('trim', array_map('strtolower', $headers));

        while (($row = fgetcsv($fp)) !== false) {
            try {
                $data = array_combine($headers, $row);
                if (!isset($data['name']) || !isset($data['price'])) continue;

                // Resolve category
                $catId = null;
                if (!empty($data['category'])) {
                    $cat = Database::fetch("SELECT id FROM categories WHERE store_id = ? AND name = ?", [$this->storeId, $data['category']]);
                    if (!$cat) {
                        $catId = Database::insert('categories', [
                            'store_id' => $this->storeId, 'name' => $data['category'], 'slug' => slugify($data['category']),
                        ]);
                    } else {
                        $catId = $cat['id'];
                    }
                }

                Database::insert('products', [
                    'store_id'    => $this->storeId,
                    'category_id' => $catId,
                    'name'        => $data['name'],
                    'slug'        => slugify($data['name']) . '-' . substr(uniqid(),-4),
                    'sku'         => $data['sku'] ?? '',
                    'price'       => (float)$data['price'],
                    'compare_price'=> !empty($data['compare_price']) ? (float)$data['compare_price'] : null,
                    'quantity'    => (int)($data['quantity'] ?? 0),
                    'description' => $data['description'] ?? '',
                    'status'      => $data['status'] ?? 'active',
                ]);
                $imported++;
            } catch (Throwable $e) {
                $errors++;
                $errorLog[] = "Row " . ($imported + $errors) . ": " . $e->getMessage();
            }
        }
        fclose($fp);

        return ['imported' => $imported, 'errors' => $errors, 'error_log' => $errorLog];
    }
}
