<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\LogService;
use RuntimeException;

final class ProductController extends Controller
{
    private ProductRepository $products;
    private CategoryRepository $categories;
    private LogService $logs;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->products = new ProductRepository($db);
        $this->categories = new CategoryRepository($db);
        $this->logs = new LogService($db);
    }

    public function index(): void
    {
        $search = trim((string) $this->request->query('q', ''));

        $this->render('products/index', [
            'title' => 'Товары',
            'search' => $search,
            'products' => $this->products->search($search),
        ]);
    }

    public function create(): void
    {
        $this->render('products/form', [
            'title' => 'Новый товар',
            'product' => null,
            'categories' => $this->categories->active(),
            'action' => '/products/store',
        ]);
    }

    public function store(): void
    {
        $this->ensureCsrf();

        $data = $this->productPayload();
        $errors = $this->validateProduct($data);

        if ($this->products->barcodeExists($data['barcode'])) {
            $errors['barcode'] = 'Товар с таким barcode уже существует.';
        }

        if ($errors !== []) {
            $this->redirectWithErrors('/products/create', $errors, $data);
        }

        $productId = $this->products->create($data);
        $this->logs->write((int) Auth::user()['id'], 'product_created', 'product', $productId, [
            'name' => $data['name'],
            'barcode' => $data['barcode'],
        ]);

        Session::flash('success', 'Товар создан.');
        $this->redirect('/products');
    }

    public function edit(): void
    {
        $id = (int) $this->request->query('id');
        $product = $this->products->find($id);

        if (!$product) {
            Session::flash('error', 'Товар не найден.');
            $this->redirect('/products');
        }

        $this->render('products/form', [
            'title' => 'Редактирование товара',
            'product' => $product,
            'categories' => $this->categories->active(),
            'action' => '/products/update?id=' . $id,
        ]);
    }

    public function update(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->query('id');
        $product = $this->products->find($id);

        if (!$product) {
            Session::flash('error', 'Товар не найден.');
            $this->redirect('/products');
        }

        $data = $this->productPayload();
        $errors = $this->validateProduct($data);

        if ($this->products->barcodeExists($data['barcode'], $id)) {
            $errors['barcode'] = 'Товар с таким barcode уже существует.';
        }

        if ($errors !== []) {
            $this->redirectWithErrors('/products/edit?id=' . $id, $errors, $data);
        }

        $this->products->update($id, $data);
        $this->logs->write((int) Auth::user()['id'], 'product_updated', 'product', $id, [
            'name' => $data['name'],
            'barcode' => $data['barcode'],
        ]);

        Session::flash('success', 'Товар обновлен.');
        $this->redirect('/products');
    }

    public function delete(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->input('id');
        $product = $this->products->find($id);

        if (!$product) {
            Session::flash('error', 'Товар не найден.');
            $this->redirect('/products');
        }

        $this->products->deactivate($id);
        $this->logs->write((int) Auth::user()['id'], 'product_deactivated', 'product', $id, [
            'name' => $product['name'],
            'barcode' => $product['barcode'],
        ]);

        Session::flash('success', 'Товар деактивирован.');
        $this->redirect('/products');
    }

    private function productPayload(): array
    {
        return [
            'name' => trim((string) $this->request->post('name')),
            'barcode' => trim((string) $this->request->post('barcode')),
            'article' => trim((string) $this->request->post('article')),
            'category_id' => $this->request->post('category_id'),
            'purchase_price' => round((float) $this->request->post('purchase_price'), 2),
            'sale_price' => round((float) $this->request->post('sale_price'), 2),
            'stock_qty' => round((float) $this->request->post('stock_qty', 0), 3),
            'unit' => trim((string) $this->request->post('unit', 'pcs')),
            'status' => (string) $this->request->post('status', 'active'),
        ];
    }

    private function validateProduct(array $data): array
    {
        $errors = Validator::validate($data, [
            'name' => ['required', 'max:255'],
            'barcode' => ['required', 'max:100'],
            'purchase_price' => ['numeric', 'min:0'],
            'sale_price' => ['numeric', 'min:0'],
            'unit' => ['required', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name' => 'Название',
            'barcode' => 'Barcode',
            'purchase_price' => 'Цена закупки',
            'sale_price' => 'Цена продажи',
            'unit' => 'Единица',
            'status' => 'Статус',
        ]);

        if ($data['category_id'] !== null && $data['category_id'] !== '') {
            if (!$this->categories->find((int) $data['category_id'])) {
                $errors['category_id'] = 'Выбрана некорректная категория.';
            }
        }

        if ($data['purchase_price'] > $data['sale_price'] && $data['sale_price'] > 0) {
            $errors['sale_price'] = 'Цена продажи должна быть не меньше закупочной.';
        }

        return $errors;
    }
}
