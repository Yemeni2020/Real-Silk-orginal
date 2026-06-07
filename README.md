<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Fira+Code&weight=600&size=28&pause=1200&color=3B82F6&center=true&vCenter=true&width=760&lines=Real+Silk;Laravel+Multi-Vendor+Commerce+Platform;AliExpress+Import+and+Marketplace+Operations" alt="Real Silk typing banner" />

  **A production-oriented e-commerce platform for in-house and vendor sales, shipping operations, multi-currency checkout, and AliExpress-assisted catalog workflows.**

  `Laravel 10` `PHP 8.1+` `MySQL` `Vue 3` `Laravel Mix`
</div>

---

## Overview

Real Silk is a Laravel-based commerce application that combines:

- In-house product management
- Multi-vendor marketplace workflows
- Customer storefront and checkout flows
- Admin and seller dashboards
- Shipping, wallet, loyalty, and reporting modules
- AliExpress import, preview, validation, and publishing tools

The repository includes installation screens, operational admin tooling, third-party payment integrations, and custom marketplace extensions tailored for day-to-day store management.

## Core Features

- Multi-vendor marketplace with separate admin, seller, customer, and delivery flows
- In-house and seller-wise shipping models
- Product, category, brand, inventory, flash deal, banner, and coupon management
- Customer wallet, loyalty points, support tickets, and order tracking
- Multi-currency support with manual and API-driven exchange-rate configuration
- Multiple payment gateway integrations including Stripe, PayPal, Paystack, Razorpay, Flutterwave, Tap, MyFatoorah, and others
- Digital product support alongside physical catalog management
- AliExpress catalog browsing, product preview, import, pricing, policy checks, and publish workflows
- Reporting for orders, earnings, product sales, stock, refunds, and transactions

## AliExpress Integration

This repository contains a custom AliExpress workflow beyond a basic import:

- OAuth-based AliExpress connection flow
- Product import by URL or product ID
- Catalog search and preview in the admin panel
- Product normalization, pricing calculation, and policy validation
- Preview persistence before import or publish
- Idempotent publishing with local mapping support
- Image normalization, download, thumbnail assignment, and republish-safe handling
- Sync commands and queue/job infrastructure for AliExpress operations

Relevant configuration lives in [config/aliexpress.php](/home/realsilk.sa/public_html/config/aliexpress.php).

## Tech Stack

### Backend

- PHP 8.1+
- Laravel 10
- Eloquent ORM
- Laravel Passport
- Laravel Sanctum
- Laravel Queues

### Frontend

- Blade templates
- Vue 3
- Inertia.js packages
- Bootstrap
- Sass
- Laravel Mix

### Database and Infrastructure

- MySQL / MariaDB
- Redis-compatible session or cache options
- Local or cloud filesystem support
- Cron-based scheduled tasks and queue workers

## Project Structure

- [app](/home/realsilk.sa/public_html/app): business logic, controllers, services, models, traits, jobs, and commands
- [routes/web/routes.php](/home/realsilk.sa/public_html/routes/web/routes.php): storefront, checkout, profile, and public flows
- [routes/admin/routes.php](/home/realsilk.sa/public_html/routes/admin/routes.php): admin, seller operations, configuration, and AliExpress management
- [config](/home/realsilk.sa/public_html/config): framework and integration configuration
- [resources/views](/home/realsilk.sa/public_html/resources/views): admin, storefront, seller, office, export, and installation views
- [database/migrations](/home/realsilk.sa/public_html/database/migrations): schema history
- [database/seeds](/home/realsilk.sa/public_html/database/seeds): bootstrap seeders

## Installation

### Requirements

- PHP 8.1 or newer
- Composer
- Node.js and npm
- MySQL or MariaDB
- Web server pointed to the project public entry

### Setup

1. Clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Create the environment file:

```bash
cp .env.example .env
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Update database and application settings in `.env`.
7. Run migrations:

```bash
php artisan migrate
```

8. Seed baseline data if required:

```bash
php artisan db:seed
```

9. Build frontend assets:

```bash
npm run prod
```

10. Create the storage symlink:

```bash
php artisan storage:link
```

11. Start the application locally if needed:

```bash
php artisan serve
```

## Environment Notes

The example environment file already includes AliExpress-related keys in [.env.example](/home/realsilk.sa/public_html/.env.example).

Common variables you will likely need to configure:

- `APP_NAME`
- `APP_URL`
- `DB_*`
- `QUEUE_CONNECTION`
- `MAIL_*`
- payment gateway credentials
- `ALIEXPRESS_APP_KEY`
- `ALIEXPRESS_APP_SECRET`
- `ALIEXPRESS_REDIRECT_URI`

## Useful Commands

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan queue:work
php artisan storage:link
php artisan currency:update
php artisan aliexpress:sync-products
php artisan aliexpress:refresh-all
php artisan aliexpress:publish-product
```

Available AliExpress and currency-related commands may depend on your environment and feature setup.

## Operational Modules

### Admin

- dashboard and analytics
- catalog and product control
- order lifecycle management
- customer and vendor administration
- shipping and delivery configuration
- payment and third-party configuration
- currency and exchange-rate management
- AliExpress catalog and preview tools

### Seller / Office

- product management
- stock and pricing updates
- order handling
- shipping visibility
- sales and transaction reporting

### Customer

- storefront browsing
- cart and checkout
- wishlist and compare
- wallet and loyalty
- support and order tracking

## Development Notes

- The application contains significant custom business logic on top of Laravel defaults.
- Route definitions are segmented under `routes/web`, `routes/admin`, `routes/install`, and shared route files.
- The current frontend stack is Mix-based, not Vite-based.
- The codebase includes both in-house and seller-specific commerce flows, so changes to product, shipping, pricing, and order logic should be reviewed across both paths.

## Security and Configuration

- Do not commit real API keys, mail credentials, payment secrets, or production `.env` values.
- Review payment, storage, mail, and AliExpress configuration before deploying.
- If you use queues, configure a persistent worker in production.

## License

This repository contains application code for the Real Silk project. Use, redistribution, and commercial rights should follow the repository owner's policy and any upstream license obligations from included dependencies.
