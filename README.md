# Store Order Inventory

Laravel 12 retail counter app: place orders, track stock, and manage customers via a Bootstrap/jQuery counter page and public JSON APIs. Pricing and tax are server-authoritative; stock is locked on order create; confirmation email is queued.

## Order Items table

`order_items` was added (not in the original requirements) so each order can store multiple line items with a snapshot of `unit_price`, `qty`, `tax_rate`, and line totals. Without it, multi-product orders and historical pricing would not be reliable after product data changes.

## Min stock level (`min_qty_level`)

On `products`, `min_qty_level` is the reorder threshold. A product is low stock when `qty <= min_qty_level`. Used by the counter low-stock panel and `GET /api/products/low-stock`.

## Scramble API docs

OpenAPI docs from [Scramble](https://github.com/dedoc/scramble):

- UI: `/docs/api`
- JSON: `/docs/api.json`

## Setup

**Needs:** PHP 8.2+, Composer, MySQL

```bash
git clone https://github.com/santhanakumar-p/store-order-inventory.git
cd store-order-inventory
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Set `DB_*` in `.env`, create the MySQL database, then:

```bash
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000
php artisan queue:work         # second terminal — for confirmation emails
```

| Resource | URL |
|----------|-----|
| Counter | `/` |
| APIs | `/api/...` |
| Docs | `/docs/api` |

**APIs:** `GET /api/customers/search?email=` · `GET /api/products` · `GET /api/products/low-stock` · `POST /api/orders` · `GET /api/orders?email=`

Optional: `php artisan test`
