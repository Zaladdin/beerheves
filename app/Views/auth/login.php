<div class="login-card">
    <div class="login-brand">
        <span class="brand-mark">BH</span>
        <div>
            <h1>BeerHeves ERP</h1>
            <p>Вход в систему учета товаров и документов</p>
        </div>
    </div>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?= e($flashError) ?></div>
    <?php endif; ?>

    <form method="post" action="/login" class="row g-3">
        <?= csrf_field() ?>

        <div class="col-12">
            <label class="form-label">Логин</label>
            <input
                type="text"
                name="username"
                class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['username'] ?? '') ?>"
                autofocus
                required
            >
            <?php if (isset($errors['username'])): ?>
                <div class="invalid-feedback"><?= e($errors['username']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label class="form-label">Пароль</label>
            <input
                type="password"
                name="password"
                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                required
            >
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?= e($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12 d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Войти</button>
        </div>
    </form>

    <div class="demo-credentials">
        <div><strong>Demo:</strong> <code>admin / admin123</code></div>
        <div><code>manager / manager123</code></div>
        <div><code>cashier / cashier123</code></div>
    </div>
</div>
