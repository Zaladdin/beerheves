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
