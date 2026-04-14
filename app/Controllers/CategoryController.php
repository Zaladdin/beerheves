<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\CategoryRepository;
use App\Services\LogService;

final class CategoryController extends Controller
{
    private CategoryRepository $categories;
    private LogService $logs;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->categories = new CategoryRepository($db);
        $this->logs = new LogService($db);
    }

    public function index(): void
    {
        $search = trim((string) $this->request->query('q', ''));

        $this->render('categories/index', [
            'title' => 'Категории',
            'search' => $search,
            'categories' => $this->categories->search($search),
        ]);
    }

    public function store(): void
    {
        $this->ensureCsrf();

        $data = [
            'name' => trim((string) $this->request->post('name')),
            'status' => (string) $this->request->post('status', 'active'),
        ];

        $errors = Validator::validate($data, [
            'name' => ['required', 'max:150'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name' => 'Название',
            'status' => 'Статус',
        ]);

        if ($this->categories->nameExists($data['name'])) {
            $errors['name'] = 'Категория с таким названием уже существует.';
        }

        if ($errors !== []) {
            $this->redirectWithErrors('/categories', $errors, $data);
        }

        $categoryId = $this->categories->create($data);
        $this->logs->write((int) Auth::user()['id'], 'category_created', 'category', $categoryId, [
            'name' => $data['name'],
        ]);

        Session::flash('success', 'Категория создана.');
        $this->redirect('/categories');
    }

    public function update(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->input('id');
        $category = $this->categories->find($id);

        if (!$category) {
            Session::flash('error', 'Категория не найдена.');
            $this->redirect('/categories');
        }

        $data = [
            'name' => trim((string) $this->request->post('name')),
            'status' => (string) $this->request->post('status', 'active'),
        ];

        $errors = Validator::validate($data, [
            'name' => ['required', 'max:150'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name' => 'Название',
            'status' => 'Статус',
        ]);

        if ($this->categories->nameExists($data['name'], $id)) {
            $errors['name'] = 'Категория с таким названием уже существует.';
        }

        if ($errors !== []) {
            Session::flash('error', reset($errors));
            $this->redirect('/categories');
        }

        $this->categories->update($id, $data);
        $this->logs->write((int) Auth::user()['id'], 'category_updated', 'category', $id, [
            'name' => $data['name'],
        ]);

        Session::flash('success', 'Категория обновлена.');
        $this->redirect('/categories');
    }

    public function delete(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->input('id');
        $category = $this->categories->find($id);

        if (!$category) {
            Session::flash('error', 'Категория не найдена.');
            $this->redirect('/categories');
        }

        $this->categories->deactivate($id);
        $this->logs->write((int) Auth::user()['id'], 'category_deactivated', 'category', $id, [
            'name' => $category['name'],
        ]);

        Session::flash('success', 'Категория деактивирована.');
        $this->redirect('/categories');
    }
}
