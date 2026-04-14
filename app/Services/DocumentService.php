<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockBalanceRepository;
use PDO;
use RuntimeException;
use Throwable;

final class DocumentService
{
    private DocumentRepository $documents;
    private ProductRepository $products;
    private StockBalanceRepository $stockBalances;
    private LogService $logs;

    public function __construct(private readonly PDO $db)
    {
        $this->documents = new DocumentRepository($db);
        $this->products = new ProductRepository($db);
        $this->stockBalances = new StockBalanceRepository($db);
        $this->logs = new LogService($db);
    }

    public function createDocument(array $documentData, array $items, array $user): int
    {
        $documentData['source_warehouse_id'] = $this->nullIfEmpty($documentData['source_warehouse_id'] ?? null);
        $documentData['target_warehouse_id'] = $this->nullIfEmpty($documentData['target_warehouse_id'] ?? null);
        $documentData['comment'] = trim((string) ($documentData['comment'] ?? ''));
        $documentData['status'] = $documentData['status'] === 'posted' ? 'posted' : 'draft';

        $this->validateWarehouses($documentData);
        $normalizedItems = $this->normalizeItems($items);

        if ($normalizedItems === []) {
            throw new RuntimeException('Документ должен содержать хотя бы одну строку товара.');
        }

        $this->db->beginTransaction();

        try {
            $documentData['doc_number'] = $this->generateDocNumber($documentData['doc_type']);
            $documentData['user_id'] = (int) $user['id'];
            $documentId = $this->documents->create($documentData);
            $this->documents->addItems($documentId, $normalizedItems);

            $document = $this->documents->find($documentId);
            if (!$document) {
                throw new RuntimeException('Не удалось загрузить созданный документ.');
            }

            if ($documentData['status'] === 'posted') {
                $this->applyStockChanges($document, $normalizedItems, 1);
                $this->logs->write((int) $user['id'], 'document_posted', 'document', $documentId, [
                    'doc_number' => $document['doc_number'],
                    'doc_type' => $document['doc_type'],
                ]);
            }

            $this->logs->write((int) $user['id'], 'document_created', 'document', $documentId, [
                'doc_number' => $document['doc_number'],
                'doc_type' => $document['doc_type'],
                'status' => $documentData['status'],
            ]);

            $this->db->commit();

            return $documentId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function postDocument(int $documentId, array $user): void
    {
        $document = $this->documents->findWithItems($documentId);

        if (!$document) {
            throw new RuntimeException('Документ не найден.');
        }

        if ($document['status'] !== 'draft') {
            throw new RuntimeException('Проводить можно только документ в статусе черновика.');
        }

        $this->db->beginTransaction();

        try {
            $this->applyStockChanges($document, $document['items'], 1);
            $this->documents->updateStatus($documentId, 'posted');

            $this->logs->write((int) $user['id'], 'document_posted', 'document', $documentId, [
                'doc_number' => $document['doc_number'],
                'doc_type' => $document['doc_type'],
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function cancelDocument(int $documentId, array $user): void
    {
        $document = $this->documents->findWithItems($documentId);

        if (!$document) {
            throw new RuntimeException('Документ не найден.');
        }

        if ($document['status'] === 'cancelled') {
            throw new RuntimeException('Документ уже отменен.');
        }

        $this->db->beginTransaction();

        try {
            if ($document['status'] === 'posted') {
                $this->applyStockChanges($document, $document['items'], -1);
            }

            $this->documents->updateStatus($documentId, 'cancelled');
            $this->logs->write((int) $user['id'], 'document_cancelled', 'document', $documentId, [
                'doc_number' => $document['doc_number'],
                'doc_type' => $document['doc_type'],
                'previous_status' => $document['status'],
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $barcode = trim((string) ($item['barcode'] ?? ''));
            $qty = round((float) ($item['qty'] ?? 0), 3);
            $price = round((float) ($item['price'] ?? 0), 2);

            if ($productId <= 0 || $barcode === '' || $qty <= 0) {
                continue;
            }

            $product = $this->products->find($productId);
            if (!$product) {
                throw new RuntimeException("Товар #{$productId} не найден.");
            }

            $normalized[] = [
                'product_id' => $productId,
                'barcode' => $barcode,
                'qty' => $qty,
                'price' => $price,
                'total' => round($qty * $price, 2),
            ];
        }

        return $normalized;
    }

    private function validateWarehouses(array $documentData): void
    {
        $type = $documentData['doc_type'] ?? '';
        $source = $documentData['source_warehouse_id'];
        $target = $documentData['target_warehouse_id'];

        if (!in_array($type, ['incoming', 'sale', 'writeoff', 'transfer'], true)) {
            throw new RuntimeException('Недопустимый тип документа.');
        }

        if ($type === 'incoming' && !$target) {
            throw new RuntimeException('Для прихода нужно указать склад назначения.');
        }

        if (in_array($type, ['sale', 'writeoff'], true) && !$source) {
            throw new RuntimeException('Для выбранного типа документа нужен исходный склад.');
        }

        if ($type === 'transfer') {
            if (!$source || !$target) {
                throw new RuntimeException('Для перемещения нужно указать оба склада.');
            }

            if ((int) $source === (int) $target) {
                throw new RuntimeException('Склад отправки и склад получения должны отличаться.');
            }
        }
    }

    private function applyStockChanges(array $document, array $items, int $direction): void
    {
        $affectedProducts = [];

        foreach ($items as $item) {
            $qty = round((float) $item['qty'] * $direction, 3);
            $productId = (int) $item['product_id'];
            $affectedProducts[] = $productId;

            switch ($document['doc_type']) {
                case 'incoming':
                    $this->changeStock((int) $document['target_warehouse_id'], $productId, $qty);
                    break;

                case 'sale':
                case 'writeoff':
                    $this->changeStock((int) $document['source_warehouse_id'], $productId, -$qty);
                    break;

                case 'transfer':
                    $this->changeStock((int) $document['source_warehouse_id'], $productId, -$qty);
                    $this->changeStock((int) $document['target_warehouse_id'], $productId, $qty);
                    break;
            }
        }

        foreach (array_unique($affectedProducts) as $productId) {
            $this->products->syncTotalStock((int) $productId);
        }
    }

    private function changeStock(int $warehouseId, int $productId, float $delta): void
    {
        $current = $this->stockBalances->getQty($warehouseId, $productId);
        $next = round($current + $delta, 3);

        if ($next < 0) {
            throw new RuntimeException('Недостаточный остаток для проведения документа.');
        }

        $this->stockBalances->adjust($warehouseId, $productId, $delta);
    }

    private function generateDocNumber(string $type): string
    {
        $prefix = match ($type) {
            'incoming' => 'IN',
            'sale' => 'SL',
            'writeoff' => 'WO',
            'transfer' => 'TR',
            default => 'DOC',
        };

        return sprintf('%s-%s-%03d', $prefix, date('Ymd-His'), random_int(100, 999));
    }

    private function nullIfEmpty(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
