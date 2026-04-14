<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockBalanceRepository;
use App\Services\LogService;

final class ApiController extends Controller
{
    private ProductRepository $products;
    private CategoryRepository $categories;
    private StockBalanceRepository $stockBalances;
    private LogService $logs;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->products = new ProductRepository($db);
        $this->categories = new CategoryRepository($db);
        $this->stockBalances = new StockBalanceRepository($db);
        $this->logs = new LogService($db);
    }

    public function findByBarcode(): void
    {
        $barcode = trim((string) $this->request->query('barcode', ''));
        $warehouseId = (int) $this->request->query('warehouse_id', 0);

        if ($barcode === '') {
            $this->json(['success' => false, 'message' => 'Barcode is empty.'], 422);
        }

        $product = $this->products->findByBarcode($barcode);

        if (!$product) {
            $this->json([
                'success' => true,
                'found' => false,
                'barcode' => $barcode,
            ]);
        }

        $product['available_qty'] = $warehouseId > 0
            ? $this->stockBalances->getQty($warehouseId, (int) $product['id'])
            : (float) $product['stock_qty'];

        $this->json([
            'success' => true,
            'found' => true,
            'product' => $product,
        ]);
    }

    public function quickCreateProduct(): void
    {
        $this->ensureCsrf(true);

        $data = [
            'name' => trim((string) $this->request->post('name')),
            'barcode' => trim((string) $this->request->post('barcode')),
            'article' => trim((string) $this->request->post('article')),
            'category_id' => $this->request->post('category_id'),
            'purchase_price' => round((float) $this->request->post('purchase_price', 0), 2),
            'sale_price' => round((float) $this->request->post('sale_price', 0), 2),
            'stock_qty' => 0,
            'unit' => trim((string) $this->request->post('unit', 'pcs')) ?: 'pcs',
            'status' => 'active',
        ];

        $errors = Validator::validate($data, [
            'name' => ['required', 'max:255'],
            'barcode' => ['required', 'max:100'],
            'purchase_price' => ['numeric', 'min:0'],
            'sale_price' => ['numeric', 'min:0'],
            'unit' => ['required', 'max:20'],
        ], [
            'name' => 'Название',
            'barcode' => 'Barcode',
            'purchase_price' => 'Цена закупки',
            'sale_price' => 'Цена продажи',
            'unit' => 'Единица',
        ]);

        if ($data['category_id'] !== null && $data['category_id'] !== '') {
            if (!$this->categories->find((int) $data['category_id'])) {
                $errors['category_id'] = 'Категория не найдена.';
            }
        }

        if ($this->products->barcodeExists($data['barcode'])) {
            $errors['barcode'] = 'Товар с таким barcode уже существует.';
        }

        if ($errors !== []) {
            $this->json([
                'success' => false,
                'message' => reset($errors),
                'errors' => $errors,
            ], 422);
        }

        $productId = $this->products->create($data);
        $product = $this->products->findByBarcode($data['barcode'], false);

        $this->logs->write((int) Auth::user()['id'], 'product_created_quick', 'product', $productId, [
            'name' => $data['name'],
            'barcode' => $data['barcode'],
        ]);

        $this->json([
            'success' => true,
            'message' => 'Товар создан.',
            'product' => $product,
        ], 201);
    }
}
