<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <title><?= e($title ?? 'BeerHeves ERP') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= e(asset_url('css/theme.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">BH</span>
            <div>
                <div class="brand-title">BeerHeves ERP</div>
                <div class="brand-subtitle">Inventory & scan</div>
            </div>
        </div>

        <nav class="nav flex-column sidebar-nav">
            <a class="nav-link <?= is_active_menu('/') ?>" href="/"><i class="bi bi-grid"></i> Dashboard</a>
            <?php if (has_role(['admin', 'manager'])): ?>
                <a class="nav-link <?= is_active_menu('/products') ?>" href="/products"><i class="bi bi-box-seam"></i> Товары</a>
                <a class="nav-link <?= is_active_menu('/categories') ?>" href="/categories"><i class="bi bi-tags"></i> Категории</a>
                <a class="nav-link <?= is_active_menu('/warehouses') ?>" href="/warehouses"><i class="bi bi-building"></i> Склады</a>
            <?php endif; ?>
            <a class="nav-link <?= is_active_menu('/documents') ?>" href="/documents"><i class="bi bi-journal-text"></i> Документы</a>
            <a class="nav-link <?= is_active_menu('/documents/scan') ?>" href="/documents/scan"><i class="bi bi-upc-scan"></i> Сканирование</a>
            <?php if (has_role(['admin', 'manager'])): ?>
                <a class="nav-link <?= is_active_menu('/reports') ?>" href="/reports"><i class="bi bi-bar-chart"></i> Отчеты</a>
                <a class="nav-link <?= is_active_menu('/logs') ?>" href="/logs"><i class="bi bi-clock-history"></i> Логи</a>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="app-main">
        <header class="app-header">
            <div>
                <h1 class="page-title"><?= e($title ?? 'BeerHeves ERP') ?></h1>
                <div class="page-subtitle">Склад, документы и сканирование штрихкодов</div>
            </div>
            <div class="header-actions">
                <div class="user-chip">
                    <span class="user-role"><?= e($currentUser['role'] ?? '') ?></span>
                    <strong><?= e($currentUser['full_name'] ?? '') ?></strong>
                </div>
                <form method="post" action="/logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-dark btn-sm">Выйти</button>
                </form>
            </div>
        </header>

        <main class="app-content">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <?= e($flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset_url('js/app.js')) ?>"></script>
<script src="<?= e(asset_url('js/scanner.js')) ?>"></script>
</body>
</html>
