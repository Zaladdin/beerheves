# BeerHeves ERP MVP

Минималистичное веб-приложение на чистом PHP 8.2+ и MySQL для учета товаров, документов движения и сканирования штрихкодов обычным keyboard-wedge scanner.

## Структура проекта

```text
app/
  Controllers/
  Core/
  Repositories/
  Services/
  Views/
config/
database/
public/
  assets/
    css/theme.css
    js/app.js
    js/scanner.js
.github/
  workflows/pages.yml
docs/
  index.html
.env.example
storage/logs/
```

## Что реализовано

- Session-based auth с ролями `admin`, `manager`, `cashier`
- CRUD товаров
- Управление категориями и складами
- Документы: `приход`, `продажа`, `списание`, `перемещение`
- Проведение документов с обновлением `stock_balances`
- Отдельная страница сканирования
- AJAX-поиск товара по barcode
- Быстрое создание товара из модального окна, если barcode не найден
- Dashboard, отчеты и журнал действий
- SQL schema и seed для локального старта

## Локальный запуск

1. Создайте базу данных MySQL, например `beerheves`.
2. Выполните SQL:

```sql
SOURCE database/schema.sql;
SOURCE database/seed.sql;
```

3. При необходимости задайте переменные окружения:

```powershell
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$env:DB_DATABASE = "beerheves"
$env:DB_USERNAME = "root"
$env:DB_PASSWORD = ""
```

4. Запустите встроенный сервер PHP из корня проекта:

```bash
php -S localhost:8000 -t public
```

5. Откройте:

```text
http://localhost:8000
```

## GitHub upload

1. Создайте репозиторий на GitHub.
2. Добавьте в корне локальный `.env` на основе `.env.example`.
3. Убедитесь, что в репозиторий не попадают секреты: `.gitignore` уже исключает `.env` и runtime-логи.
4. Загрузите проект в ветку `main`.

## GitHub Pages

В репозитории уже есть статическая preview-страница в `docs/index.html` и workflow `.github/workflows/pages.yml`.

Важно:

- GitHub Pages не исполняет PHP.
- GitHub Pages не дает MySQL.
- Поэтому на Pages будет открываться только статическая витрина проекта, а не рабочий backend.

Чтобы включить Pages:

1. Откройте `Settings -> Pages`.
2. Выберите source `GitHub Actions`.
3. После push в `main` workflow задеплоит содержимое `docs/`.

## Где размещать рабочее приложение

Полный PHP backend размещайте на хостинге с:

- PHP 8.2+
- MySQL
- document root -> `public/`

Подойдут любой VPS, shared hosting с PHP/MySQL, либо платформы вроде Render/Railway/Cloud hosting с PHP runtime.

## Демо-пользователи

- `admin / admin123`
- `manager / manager123`
- `cashier / cashier123`

## База данных

- `users` - пользователи и роли
- `categories` - категории товаров
- `products` - справочник товаров
- `warehouses` - склады
- `documents` - заголовки документов
- `document_items` - строки документов
- `stock_balances` - остатки по складам
- `logs` - аудит действий

## Следующие шаги

- Печать этикеток и шаблоны ценников
- Поддержка QR-кодов и DataMatrix
- Камерное сканирование на мобильных устройствах
- Импорт/экспорт Excel
- REST API для внешних систем
- Пользовательское управление ролями и правами
- Редактирование сохраненных черновиков
