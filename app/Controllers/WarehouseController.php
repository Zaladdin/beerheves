<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\WarehouseRepository;
use App\Services\LogService;

final class WarehouseController extends Controller
{
    private WarehouseRepository $warehouses;
    private LogService $logs;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->warehouses = new WarehouseRepository($db);
        $this->logs = new LogService($db);
    }

    public function index(): void
    {
        $search = trim((string) $this->request->query('q', ''));

        $this->render('warehouses/index', [
            'title' => 'Склады',
            'search' => $search,
            'warehouses' => $this->warehouses->search($search),
        ]);
    }

    public function store(): void
    {
        $this->ensureCsrf();

        $data = [
            'name' => trim((string) $this->request->post('name')),
            'location' => trim((string) $this->request->post('location')),
            'status' => (string) $this->request->post('status', 'active'),
        ];

        $errors = Validator::validate($data, [
            'name' => ['required', 'max:150'],
            'location' => ['max:255'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name' => 'Название',
            'location' => 'Локация',
            'status' => 'Статус',
        ]);

        if ($this->warehouses->nameExists($data['name'])) {
            $errors['name'] = 'Склад с таким названием уже существует.';
        }

        if ($errors !== []) {
            $this->redirectWithErrors('/warehouses', $errors, $data);
        }

        $warehouseId = $this->warehouses->create($data);
        $this->logs->write((int) Auth::user()['id'], 'warehouse_created', 'warehouse', $warehouseId, [
            'name' => $data['name'],
        ]);

        Session::flash('success', 'Склад создан.');
        $this->redirect('/warehouses');
    }

    public function update(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->input('id');
        $warehouse = $this->warehouses->find($id);

        if (!$warehouse) {
            Session::flash('error', 'Склад не найден.');
            $this->redirect('/warehouses');
        }

        $data = [
            'name' => trim((string) $this->request->post('name')),
            'location' => trim((string) $this->request->post('location')),
            'status' => (string) $this->request->post('status', 'active'),
        ];

        $errors = Validator::validate($data, [
            'name' => ['required', 'max:150'],
            'location' => ['max:255'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name' => 'Название',
            'location' => 'Локация',
            'status' => 'Статус',
        ]);

        if ($this->warehouses->nameExists($data['name'], $id)) {
            $errors['name'] = 'Склад с таким названием уже существует.';
        }

        if ($errors !== []) {
            Session::flash('error', reset($errors));
            $this->redirect('/warehouses');
        }

        $this->warehouses->update($id, $data);
        $this->logs->write((int) Auth::user()['id'], 'warehouse_updated', 'warehouse', $id, [
            'name' => $data['name'],
        ]);

        Session::flash('success', 'Склад обновлен.');
        $this->redirect('/warehouses');
    }

    public function delete(): void
    {
        $this->ensureCsrf();

        $id = (int) $this->request->input('id');
        $warehouse = $this->warehouses->find($id);

        if (!$warehouse) {
            Session::flash('error', 'Склад не найден.');
            $this->redirect('/warehouses');
        }

        $this->warehouses->deactivate($id);
        $this->logs->write((int) Auth::user()['id'], 'warehouse_deactivated', 'warehouse', $id, [
            'name' => $warehouse['name'],
        ]);

        Session::flash('success', 'Склад деактивирован.');
        $this->redirect('/warehouses');
    }
}
