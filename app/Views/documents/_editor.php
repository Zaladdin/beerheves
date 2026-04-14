<?php
$editorData = array_merge($documentDefaults ?? [], $old ?? []);
$initialItemsJson = $old['items_payload'] ?? '[]';
?>

<div
    class="document-editor"
    data-document-editor
    data-lookup-url="/api/products/barcode"
    data-quick-create-url="/api/products/quick-create"
    data-initial-items="<?= e($initialItemsJson) ?>"
>
    <form method="post" action="/documents/store" id="document-form" class="row g-4">
        <?= csrf_field() ?>
        <input type="hidden" name="items_payload" id="items-payload">
        <input type="hidden" name="status" id="document-status" value="draft">
        <input type="hidden" name="return_to" value="<?= e($editorData['return_to'] ?? '/documents/create') ?>">

        <div class="col-xl-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <div class="section-title">Параметры документа</div>
                </div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Тип документа</label>
                        <select name="doc_type" class="form-select" id="doc-type-select">
                            <option value="incoming" <?= selected('incoming', $editorData['doc_type'] ?? 'incoming') ?>>Приход</option>
                            <option value="sale" <?= selected('sale', $editorData['doc_type'] ?? '') ?>>Продажа</option>
                            <option value="writeoff" <?= selected('writeoff', $editorData['doc_type'] ?? '') ?>>Списание</option>
                            <option value="transfer" <?= selected('transfer', $editorData['doc_type'] ?? '') ?>>Перемещение</option>
                        </select>
                    </div>

                    <div class="col-12" data-source-warehouse-wrap>
                        <label class="form-label">Склад отправки</label>
                        <select name="source_warehouse_id" class="form-select" id="source-warehouse-select">
                            <option value="">Выберите склад</option>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?= e($warehouse['id']) ?>" <?= selected($warehouse['id'], $editorData['source_warehouse_id'] ?? '') ?>><?= e($warehouse['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12" data-target-warehouse-wrap>
                        <label class="form-label">Склад получения</label>
                        <select name="target_warehouse_id" class="form-select" id="target-warehouse-select">
                            <option value="">Выберите склад</option>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?= e($warehouse['id']) ?>" <?= selected($warehouse['id'], $editorData['target_warehouse_id'] ?? '') ?>><?= e($warehouse['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Комментарий</label>
                        <textarea name="comment" rows="4" class="form-control"><?= e($editorData['comment'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-dark flex-grow-1" data-submit-status="draft">Сохранить черновик</button>
                        <button type="submit" class="btn btn-primary flex-grow-1" data-submit-status="posted">Сохранить и провести</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="scan-hero">
                        <div>
                            <div class="scan-label">Barcode scanner input</div>
                            <h2 class="scan-title">Сканируйте товар или введите barcode вручную</h2>
                            <div class="scan-note">Фокус всегда возвращается в поле после добавления позиции.</div>
                        </div>
                        <div class="scanner-field-wrap">
                            <input type="text" id="barcode-input" class="form-control scanner-input" placeholder="Сканируйте штрихкод и нажмите Enter" autocomplete="off" data-autofocus>
                        </div>
                    </div>
                    <div id="scan-feedback" class="scan-feedback mt-3">Ожидание сканирования...</div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="section-title">Товары документа</div>
                    <div class="text-muted small">Повторный скан увеличивает количество</div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="document-items-table">
                        <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Товар</th>
                            <th>Артикул</th>
                            <th class="text-end">Остаток</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Цена</th>
                            <th class="text-end">Сумма</th>
                            <th class="text-end"></th>
                        </tr>
                        </thead>
                        <tbody id="document-items-body">
                        <tr class="empty-row">
                            <td colspan="8" class="text-center text-muted py-5">Отсканируйте товар, чтобы добавить его в документ</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Итого</th>
                            <th class="text-end" id="document-grand-total">$0.00</th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="quickProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="quick-product-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Товар не найден. Добавить новый?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Название</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Артикул</label>
                            <input type="text" name="article" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Категория</label>
                            <select name="category_id" class="form-select">
                                <option value="">Без категории</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ед. изм.</label>
                            <input type="text" name="unit" class="form-control" value="pcs">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Цена закупки</label>
                            <input type="number" step="0.01" min="0" name="purchase_price" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Цена продажи</label>
                            <input type="number" step="0.01" min="0" name="sale_price" class="form-control" value="0.00">
                        </div>
                        <div class="col-12">
                            <div class="alert alert-danger d-none mb-0" id="quick-product-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать и добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
