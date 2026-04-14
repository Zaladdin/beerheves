<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Repositories\CategoryRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\WarehouseRepository;
use App\Services\DocumentService;
use RuntimeException;

final class DocumentController extends Controller
{
    private DocumentRepository $documents;
    private WarehouseRepository $warehouses;
    private CategoryRepository $categories;
    private DocumentService $documentService;

    public function __construct($db, $request, $config)
    {
        parent::__construct($db, $request, $config);
        $this->documents = new DocumentRepository($db);
        $this->warehouses = new WarehouseRepository($db);
        $this->categories = new CategoryRepository($db);
        $this->documentService = new DocumentService($db);
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) $this->request->query('q', '')),
            'doc_type' => trim((string) $this->request->query('doc_type', '')),
            'status' => trim((string) $this->request->query('status', '')),
        ];

        $this->render('documents/index', [
            'title' => 'Документы',
            'documents' => $this->documents->search($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        $this->render('documents/form', [
            'title' => 'Новый документ',
            'pageMode' => 'create',
            'documentDefaults' => [
                'doc_type' => $this->request->query('doc_type', 'incoming'),
                'source_warehouse_id' => '',
                'target_warehouse_id' => '',
                'comment' => '',
                'return_to' => '/documents/create',
            ],
            'warehouses' => $this->warehouses->active(),
            'categories' => $this->categories->active(),
        ]);
    }

    public function scan(): void
    {
        $this->render('documents/scan', [
            'title' => 'Сканирование',
            'pageMode' => 'scan',
            'documentDefaults' => [
                'doc_type' => $this->request->query('doc_type', 'sale'),
                'source_warehouse_id' => '',
                'target_warehouse_id' => '',
                'comment' => '',
                'return_to' => '/documents/scan',
            ],
            'warehouses' => $this->warehouses->active(),
            'categories' => $this->categories->active(),
        ]);
    }

    public function store(): void
    {
        $this->ensureCsrf();

        $returnTo = (string) $this->request->post('return_to', '/documents/create');
        $payload = [
            'doc_type' => (string) $this->request->post('doc_type', 'incoming'),
            'source_warehouse_id' => $this->request->post('source_warehouse_id'),
            'target_warehouse_id' => $this->request->post('target_warehouse_id'),
            'comment' => trim((string) $this->request->post('comment')),
            'status' => (string) $this->request->post('status', 'draft'),
        ];

        $items = json_decode((string) $this->request->post('items_payload', '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }

        try {
            $documentId = $this->documentService->createDocument($payload, $items, (array) $this->configUser());
            Session::flash('success', $payload['status'] === 'posted' ? 'Документ сохранен и проведен.' : 'Документ сохранен как черновик.');
            $this->redirect('/documents/show?id=' . $documentId);
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Session::flash('old', array_merge($payload, ['items_payload' => json_encode($items, JSON_UNESCAPED_UNICODE)]));
            $this->redirect($returnTo);
        }
    }

    public function show(): void
    {
        $id = (int) $this->request->query('id');
        $document = $this->documents->findWithItems($id);

        if (!$document) {
            Session::flash('error', 'Документ не найден.');
            $this->redirect('/documents');
        }

        $this->render('documents/show', [
            'title' => 'Просмотр документа',
            'document' => $document,
        ]);
    }

    public function post(): void
    {
        $this->ensureCsrf();

        try {
            $this->documentService->postDocument((int) $this->request->input('id'), (array) $this->configUser());
            Session::flash('success', 'Документ проведен.');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
        }

        $this->redirect('/documents');
    }

    public function cancel(): void
    {
        $this->ensureCsrf();

        try {
            $this->documentService->cancelDocument((int) $this->request->input('id'), (array) $this->configUser());
            Session::flash('success', 'Документ отменен.');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
        }

        $this->redirect('/documents');
    }

    private function configUser(): array
    {
        return \App\Core\Auth::user() ?? [];
    }
}
