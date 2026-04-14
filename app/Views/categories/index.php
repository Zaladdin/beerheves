<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <div class="section-title">Новая категория</div>
            </div>
            <div class="card-body">
                <form method="post" action="/categories/store" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-12">
                        <label class="form-label">Название</label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e($old['name'] ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= selected('active', $old['status'] ?? 'active') ?>>Активная</option>
                            <option value="inactive" <?= selected('inactive', $old['status'] ?? '') ?>>Неактивная</option>
                        </select>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="get" action="/categories" class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control" placeholder="Поиск категории" value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-dark w-100">Найти</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($categories === []): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Категории не найдены</td></tr>
                        <?php endif; ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= e($category['id']) ?></td>
                                <td><?= e($category['name']) ?></td>
                                <td><span class="badge <?= e(status_badge_class($category['status'])) ?>"><?= e($category['status']) ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#categoryModal<?= e($category['id']) ?>">Редактировать</button>
                                    <form method="post" action="/categories/delete" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger" data-confirm="Деактивировать категорию?">Деактивировать</button>
                                    </form>
                                    <div class="modal fade" id="categoryModal<?= e($category['id']) ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="/categories/update">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Редактировать категорию</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Название</label>
                                                            <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Статус</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" <?= selected('active', $category['status']) ?>>Активная</option>
                                                                <option value="inactive" <?= selected('inactive', $category['status']) ?>>Неактивная</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Закрыть</button>
                                                        <button class="btn btn-primary">Сохранить</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
