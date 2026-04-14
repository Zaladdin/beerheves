<?php $filters = $filters ?? ['q' => '', 'doc_type' => '', 'status' => '']; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
            <form method="get" action="/documents" class="row g-2">
                <div class="col-md-auto">
                    <input type="text" name="q" class="form-control" placeholder="Номер или комментарий" value="<?= e($filters['q']) ?>">
                </div>
                <div class="col-md-auto">
                    <select name="doc_type" class="form-select">
                        <option value="">Все типы</option>
                        <option value="incoming" <?= selected('incoming', $filters['doc_type']) ?>>Приход</option>
                        <option value="sale" <?= selected('sale', $filters['doc_type']) ?>>Продажа</option>
                        <option value="writeoff" <?= selected('writeoff', $filters['doc_type']) ?>>Списание</option>
                        <option value="transfer" <?= selected('transfer', $filters['doc_type']) ?>>Перемещение</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <select name="status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="draft" <?= selected('draft', $filters['status']) ?>>Черновик</option>
                        <option value="posted" <?= selected('posted', $filters['status']) ?>>Проведен</option>
                        <option value="cancelled" <?= selected('cancelled', $filters['status']) ?>>Отменен</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-outline-dark">Фильтр</button>
                </div>
            </form>

            <div class="d-flex gap-2">
                <a href="/documents/create" class="btn btn-outline-dark">Новый документ</a>
                <a href="/documents/scan" class="btn btn-primary">Страница сканирования</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Номер</th>
                    <th>Тип</th>
                    <th>Склад отправки</th>
                    <th>Склад получения</th>
                    <th>Статус</th>
                    <th>Пользователь</th>
                    <th>Дата</th>
                    <th class="text-end">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($documents === []): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Документы не найдены</td></tr>
                <?php endif; ?>
                <?php foreach ($documents as $document): ?>
                    <tr>
                        <td><a href="/documents/show?id=<?= e($document['id']) ?>" class="fw-semibold text-decoration-none"><?= e($document['doc_number']) ?></a></td>
                        <td><?= e(doc_type_label($document['doc_type'])) ?></td>
                        <td><?= e($document['source_warehouse_name'] ?: '-') ?></td>
                        <td><?= e($document['target_warehouse_name'] ?: '-') ?></td>
                        <td><span class="badge <?= e(status_badge_class($document['status'])) ?>"><?= e(document_status_label($document['status'])) ?></span></td>
                        <td><?= e($document['user_name']) ?></td>
                        <td><?= e(date('d.m.Y H:i', strtotime($document['created_at']))) ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="/documents/show?id=<?= e($document['id']) ?>" class="btn btn-sm btn-outline-dark">Открыть</a>
                                <?php if ($document['status'] === 'draft'): ?>
                                    <form method="post" action="/documents/post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($document['id']) ?>">
                                        <button class="btn btn-sm btn-outline-success">Провести</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($document['status'] !== 'cancelled'): ?>
                                    <form method="post" action="/documents/cancel" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($document['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger" data-confirm="Отменить документ?">Отменить</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
