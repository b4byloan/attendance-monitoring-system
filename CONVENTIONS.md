# CONVENTIONS.md

Programming conventions for a **pure Laravel 12 + MySQL** application: Blade, `View\Components`, Tailwind CSS, Vite. No React, no Inertia, no Livewire. Interactivity is plain JavaScript modules, with Alpine.js allowed as the only optional micro-library.

> Rule of thumb: **if Laravel has a first-party way to do it, use it.** Add a package only when the framework genuinely lacks the feature (e.g. Excel exports).

---

## Table of Contents

1. [Core Principles](#1-core-principles)
2. [Stack and Versions](#2-stack-and-versions)
3. [Directory Structure](#3-directory-structure)
4. [Naming Conventions](#4-naming-conventions)
5. [Routing](#5-routing)
6. [Controllers](#6-controllers)
7. [Form Requests](#7-form-requests)
8. [Actions](#8-actions)
9. [Services](#9-services)
10. [Utilities](#10-utilities)
11. [Models](#11-models)
12. [Database and MySQL](#12-database-and-mysql)
13. [Enums](#13-enums)
14. [Observers](#14-observers)
15. [Rules](#15-rules)
16. [Exports](#16-exports)
17. [API Resources](#17-api-resources)
18. [Middleware](#18-middleware)
19. [Notifications](#19-notifications)
20. [Views, View Components and Composers](#20-views-view-components-and-composers)
21. [Frontend Structure: resources/views, js, css](#21-frontend-structure)
22. [Tailwind CSS Rules and Principles](#22-tailwind-css-rules-and-principles)
23. [Component Library: Forms, Tools, Widgets, Graphs](#23-component-library)
24. [Testing](#24-testing)
25. [Security and Performance](#25-security-and-performance)
26. [Code Style and Tooling](#26-code-style-and-tooling)
27. [Checklists](#27-checklists)

---

## 1. Core Principles

1. **Thin controllers, thin models.** Controllers coordinate; they do not contain business logic. Models describe data, relations, scopes and casts.
2. **One class, one job.** An Action does one thing. A Service groups one domain. A Utility is stateless.
3. **Explicit over magic.** Type-hint everything, declare return types, use enums instead of strings, use `casts()` instead of accessors for simple conversions.
4. **Validation lives in Form Requests**, never inline in controllers (except trivial one-off `Request::validate` in a closure route).
5. **Authorization lives in Policies/Gates**, invoked from Form Requests or controllers via `$this->authorize()`.
6. **Views are dumb.** No queries, no business logic, no `@php` blocks beyond trivial formatting. Data arrives prepared (View Composers, View Components, or the controller).
7. **Reusable UI is a component.** If markup appears three times, it becomes a Blade component under `app/View/Components`.
8. **Everything is testable.** If a class is hard to test, it is doing too much.
9. **Database is the source of truth for integrity.** Use foreign keys, unique indexes and `NOT NULL`; do not rely on application code alone.
10. **Consistency beats cleverness.** Follow this file even when you prefer another style.

---

## 2. Stack and Versions

| Layer          | Choice                                                         |
| -------------- | -------------------------------------------------------------- |
| PHP            | 8.3+ (8.2 minimum for Laravel 12)                              |
| Framework      | Laravel 12.x                                                   |
| Database       | MySQL 8.0+ (InnoDB, `utf8mb4`, `utf8mb4_unicode_ci`)           |
| Templates      | Blade + class-based `View\Components`                          |
| CSS            | Tailwind CSS (v4, via `@tailwindcss/vite`)                     |
| JS bundler     | Vite (Laravel Vite plugin)                                     |
| JS             | ES modules; Alpine.js optional for UI state; Chart.js for charts |
| Queue          | `database` driver by default, Redis when scale demands it      |
| Cache/Session  | `database` or `redis`                                          |
| Testing        | Pest (preferred) or PHPUnit                                    |
| Style          | Laravel Pint (`laravel` preset) + `declare(strict_types=1)`    |
| Static analysis| Larastan, level 6+                                             |

Laravel 12 specifics you must follow:

- Middleware, exceptions and routing are configured in **`bootstrap/app.php`**. There is no `Http/Kernel.php`.
- Service providers are registered in **`bootstrap/providers.php`**.
- Model casts use the **`casts(): array` method**, not the `$casts` property.
- Use `php artisan make:*` generators first, then edit.

---

## 3. Directory Structure

```
app/
├── Actions/                 # Single-purpose business operations
│   ├── Orders/
│   │   ├── CreateOrderAction.php
│   │   └── CancelOrderAction.php
│   └── Users/
├── Console/
│   └── Commands/            # Artisan commands (thin, delegate to Actions)
├── Enums/                   # Backed enums (string/int)
├── Exports/                 # Excel/CSV export classes
├── Http/
│   ├── Controllers/
│   │   ├── Api/             # JSON controllers (versioned: Api/V1)
│   │   ├── Admin/           # Back-office controllers
│   │   └── Controller.php
│   ├── Middleware/          # Custom middleware (singular folder name in Laravel)
│   ├── Requests/            # Form Requests, grouped by domain
│   └── Resources/           # API Resources + ResourceCollections
├── Models/
│   └── Concerns/            # Model traits (HasUuid, Searchable, ...)
├── Notifications/
├── Observers/
├── Policies/
├── Providers/
├── Rules/                   # Custom validation rules
├── Services/                # Domain services & third-party integrations
├── Utilities/               # Stateless static helpers
└── View/
    ├── Components/          # Class-based Blade components (see §23)
    │   ├── Forms/
    │   ├── Tools/
    │   ├── Widgets/
    │   ├── Graphs/
    │   └── Layouts/
    └── Composers/           # View composers

bootstrap/
├── app.php                  # Middleware, routing, exceptions
└── providers.php

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
│   ├── app.css              # Tailwind entry
│   ├── base/                # Resets, typography
│   ├── components/          # @apply-based component classes (sparingly)
│   └── themes/              # Design tokens per theme
├── js/
│   ├── app.js               # Entry
│   ├── bootstrap.js
│   ├── components/          # One JS module per interactive component
│   ├── graphs/              # Chart setup helpers
│   ├── pages/               # Page-specific scripts
│   └── utils/
└── views/
    ├── components/          # Anonymous + class-component templates
    │   ├── forms/
    │   ├── tools/
    │   ├── widgets/
    │   ├── graphs/
    │   └── layouts/
    ├── layouts/             # app.blade.php, guest.blade.php, admin.blade.php
    ├── partials/            # Non-reusable fragments (header, footer, sidebar)
    ├── pages/               # Full pages, grouped by feature
    │   ├── dashboard/
    │   ├── orders/
    │   └── users/
    ├── emails/              # Mailable views
    ├── notifications/       # Notification mail/markdown views
    ├── exports/             # Blade views for FromView exports (if used)
    └── errors/              # 403, 404, 419, 500, 503

routes/
├── web.php
├── api.php
├── console.php
└── channels.php

tests/
├── Feature/
├── Unit/
└── Browser/                 # Optional (Dusk/Pest browser)
```

**Rules**

- Create folders **by domain** (`Orders`, `Users`) inside `Actions`, `Requests`, `Resources`, `Notifications`, `Exports`, `Services`.
- Do not create folders that Laravel does not need until there is something to put in them.
- Never put business classes directly under `app/` root.

---

## 4. Naming Conventions

| Thing                  | Convention                                  | Example                                   |
| ---------------------- | ------------------------------------------- | ----------------------------------------- |
| Class                  | `PascalCase`                                | `OrderService`                            |
| Method / variable      | `camelCase`                                 | `calculateTotal()`                        |
| Constant / enum case   | `PascalCase` for enum cases, `UPPER_SNAKE` for class constants | `OrderStatus::Pending`, `MAX_ITEMS` |
| DB table               | `snake_case`, **plural**                    | `order_items`                             |
| Pivot table            | singular models, alphabetical, `snake_case` | `product_tag`                             |
| DB column              | `snake_case`                                | `shipped_at`                              |
| Foreign key            | `{singular_table}_id`                       | `user_id`                                 |
| Model                  | Singular `PascalCase`                       | `OrderItem`                               |
| Controller             | Singular + `Controller`                     | `OrderController`                         |
| Invokable controller   | Verb phrase + `Controller`                  | `ExportOrdersController`                  |
| Form Request           | `{Verb}{Model}Request`                      | `StoreOrderRequest`, `UpdateOrderRequest` |
| Action                 | `{Verb}{Noun}Action`                        | `CreateOrderAction`                       |
| Service                | `{Domain}Service`                           | `PaymentService`                          |
| Utility                | `{Topic}` or `{Topic}Util`                  | `Money`, `StrUtil`                        |
| Rule                   | Descriptive noun/adjective                  | `ValidPhilippineMobile`                   |
| Export                 | `{Plural}Export`                            | `OrdersExport`                            |
| Enum                   | Singular noun                               | `OrderStatus`                             |
| Observer               | `{Model}Observer`                           | `OrderObserver`                           |
| Resource               | `{Model}Resource` / `{Model}Collection`     | `OrderResource`                           |
| Middleware             | Descriptive                                 | `EnsureUserIsActive`                      |
| Notification           | Past-tense event                            | `OrderShipped`                            |
| Composer               | `{Topic}Composer`                           | `NavigationComposer`                      |
| Blade view file        | `kebab-case.blade.php`                      | `order-summary.blade.php`                 |
| Blade component class  | `PascalCase`                                | `Forms\InputPassword`                     |
| Blade component tag    | `kebab-case` with dot namespaces            | `<x-forms.input-password />`              |
| Route name             | `dot.separated`, plural resource            | `orders.index`                            |
| Route URI              | `kebab-case`, plural                        | `/order-items`                            |
| Config key             | `snake_case`                                | `config('shop.tax_rate')`                 |
| JS file                | `kebab-case.js`                             | `date-range.js`                           |
| CSS custom property    | `--kebab-case`                              | `--color-primary-500`                     |

---

## 5. Routing

- **Web routes** in `routes/web.php`, **API routes** in `routes/api.php` (prefixed `/api` automatically).
- Use **resource routes** and `Route::resource()` / `apiResource()` wherever the convention fits.
- Group by middleware, prefix and name, and **always name routes**.
- Use **route model binding** (implicit by default; explicit via `Route::bind` in a provider only when needed).
- Use `scopeBindings()` for nested resources.
- Never use closures for anything beyond trivial views (closures cannot be route-cached).

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::get('orders-export', ExportOrdersController::class)->name('orders.export');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', Admin\UserController::class);
    });
});
```

**Rules**

- Non-CRUD behaviour gets its **own invokable controller** rather than extra methods on a resource controller.
- API versioning: `routes/api.php` → `Route::prefix('v1')->name('api.v1.')`.
- Use `Route::fallback()` only for API JSON 404s.

---

## 6. Controllers

Location: `app/Http/Controllers` (`Admin/`, `Api/V1/` subfolders when needed).

**A controller method should be ≤ 15 lines.** Its job:

1. Receive a validated Form Request.
2. Call an Action or Service.
3. Return a view, redirect or resource.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('pages.orders.index', [
            'orders' => Order::query()->with('customer')->latest()->paginate(15),
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $order = $action->execute($request->validated(), $request->user());

        return redirect()
            ->route('orders.show', $order)
            ->with('success', __('orders.created'));
    }
}
```

**Rules**

- Inject dependencies in **method signatures**, or the constructor for dependencies used by every method.
- Always declare return types.
- Never use `$request->all()`; use `$request->validated()`.
- Never query in a controller beyond simple reads for an index/show page. If a query exceeds a `where`/`with`/`paginate` chain, move it to a model scope or a Service.
- Use **flash keys** consistently: `success`, `error`, `warning`, `info`.

---

## 7. Form Requests

Location: `app/Http/Requests/{Domain}/`.

```php
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'status'      => ['required', Rule::enum(OrderStatus::class)],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:999'],
            'phone'       => ['nullable', new ValidPhilippineMobile()],
        ];
    }

    public function attributes(): array
    {
        return ['items.*.product_id' => 'product'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => preg_replace('/\D+/', '', (string) $this->phone)]);
    }
}
```

**Rules**

- Use **array syntax** for rules (required for `Rule` objects, and consistent).
- Use `Rule::enum()`, `Rule::exists()`, `Rule::unique()->ignore()` rather than string forms when they take parameters.
- Normalize input in `prepareForValidation()`.
- Messages come from **lang files**, not hard-coded strings.
- Separate `Store…` and `Update…` requests; share rules through a trait only if they are truly identical.

---

## 8. Actions

Location: `app/Actions/{Domain}/`. An Action is a **single-purpose, use-case class** with one public method: **`execute()`**.

```php
final class CreateOrderAction
{
    public function __construct(
        private readonly InventoryService $inventory,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor): Order
    {
        return DB::transaction(function () use ($data, $actor): Order {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'status'      => OrderStatus::Pending,
                'created_by'  => $actor->id,
            ]);

            $order->items()->createMany($data['items']);
            $this->inventory->reserve($order);

            return $order->refresh();
        });
    }
}
```

**Rules**

- Name: `{Verb}{Noun}Action`. The verb states the use case (`Create`, `Cancel`, `Import`, `Approve`).
- **Transactions belong in Actions** (or Services), never controllers.
- Actions may call other Actions and Services; never controllers, requests or views.
- Accept **plain arrays / DTO / models**, not `Request` objects. This keeps them callable from controllers, commands, jobs and tests.
- Mark `final`. Resolve through the container (type-hint), do not `new` them.

---

## 9. Services

Location: `app/Services/` (subfolders by domain or integration).

A Service groups **related operations for one domain or external system** (e.g., `PaymentService`, `InventoryService`, `Sms/SemaphoreClient`).

```php
class PaymentService
{
    public function __construct(
        private readonly PendingRequest $http,
        private readonly string $secret,
    ) {}

    public function charge(Order $order): PaymentResult { /* ... */ }
    public function refund(Payment $payment, int $amountInCentavos): PaymentResult { /* ... */ }
}
```

**Rules**

- Bind in a Service Provider (`AppServiceProvider::register()`), reading credentials from `config/services.php`, **never `env()` outside config files**.
- Services may be stateful through constructor-injected dependencies, but must not hold request state.
- When a third-party API is involved, put it behind an **interface** (`PaymentGateway`) so it can be faked in tests.
- Actions = *what the app does*. Services = *capabilities the Actions use*.

---

## 10. Utilities

Location: `app/Utilities/`. **Stateless, pure helpers** with static methods and no dependencies on the container or database.

```php
final class Money
{
    public static function format(int $centavos, string $currency = '₱'): string
    {
        return $currency . number_format($centavos / 100, 2);
    }
}
```

**Rules**

- Utilities never touch the database, HTTP or the filesystem. If they do, they are a Service.
- Group by topic: `Money`, `Dates`, `StrUtil`, `Phone`.
- Do **not** add global functions via `helpers.php` unless a function is used in Blade everywhere. If you must, autoload through `composer.json` `files`.

---

## 11. Models

Location: `app/Models`; shared traits in `app/Models/Concerns`.

```php
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'status', 'total', 'shipped_at'];

    protected function casts(): array
    {
        return [
            'status'     => OrderStatus::class,
            'total'      => 'integer',
            'meta'       => 'array',
            'shipped_at' => 'datetime',
        ];
    }

    /* Relationships */
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany      { return $this->hasMany(OrderItem::class); }

    /* Scopes */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', OrderStatus::Pending);
    }

    /* Accessors / Mutators */
    protected function totalFormatted(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->total));
    }
}
```

**Order of members**: traits → constants → properties (`$fillable`, `$hidden`) → `casts()` → relationships → scopes → accessors/mutators → other methods.

**Rules**

- Use **`$fillable`** (never `$guarded = []`).
- Relationships have **explicit return types** and are named camelCase (`orderItems`, not `order_items`).
- Prevent lazy loading in non-production: `Model::preventLazyLoading(! app()->isProduction())` in `AppServiceProvider::boot()`. Also `Model::shouldBeStrict()` in non-production.
- **Eager load** (`with()`) every relationship that a view will touch.
- Use scopes for reusable query fragments; name them for what they filter (`pending`, `forCustomer`).
- No business workflows in models. A method such as `markAsShipped()` that only sets attributes is fine; anything touching other aggregates belongs to an Action.
- Use **`SoftDeletes`** where records must be recoverable or referenced by history.
- Money is stored as **integer minor units** (centavos), never float.

---

## 12. Database and MySQL

### Configuration

```env
DB_CONNECTION=mysql
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

In `config/database.php` keep `'engine' => 'InnoDB'` and `'strict' => true`.

### Migrations

- **One migration per change.** Never edit a migration that has run in a shared environment; add a new one.
- Name by intent: `create_orders_table`, `add_status_to_orders_table`.
- Always implement `down()` when it is safely reversible.

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->restrictOnDelete();
    $table->string('status', 20)->index();
    $table->unsignedBigInteger('total')->default(0);   // centavos
    $table->json('meta')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['customer_id', 'status']);
});
```

### Rules

- Use `foreignId()->constrained()` and choose the delete behavior explicitly (`cascadeOnDelete`, `restrictOnDelete`, `nullOnDelete`).
- Index every column used in `WHERE`, `ORDER BY`, `JOIN`; use **composite indexes** with the most selective column first.
- Enum-like columns: store as `string` (backed by a PHP enum), **not** MySQL `ENUM` (altering MySQL ENUMs is painful).
- Use `decimal` only for true decimals; prefer integers for money.
- Use `json` for flexible metadata, never for data you filter or join on frequently.
- Use `ulid()`/`uuid()` for public identifiers if IDs must not be guessable; keep auto-increment `id` as the primary key.
- Seed reference data in **seeders**, not migrations.
- Factories exist for every model; seeders compose factories.
- Avoid raw SQL; when needed, use bindings (`DB::select('... where id = ?', [$id])`).
- For heavy reads use `chunkById()` / `lazyById()` / `cursor()`; never `->get()` on unbounded tables.
- N+1 queries are bugs. Verify with `preventLazyLoading` and the query log in tests.

---

## 13. Enums

Location: `app/Enums`. Always **backed** enums, with helper methods for display.

```php
enum OrderStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Shipped   = 'shipped';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.order_status.' . $this->value);
    }

    /** Tailwind color key consumed by <x-widgets.badge /> */
    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Paid      => 'info',
            self::Shipped   => 'success',
            self::Cancelled => 'danger',
        };
    }

    /** @return array<string, string> value => label (for selects) */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
```

**Rules**

- Cases are `PascalCase`; values are `snake_case` strings (or ints when mapping legacy data).
- Cast on the model; validate with `Rule::enum()`.
- **Never compare against raw strings** (`'pending'`); use `OrderStatus::Pending`.
- Put presentation (label, color, icon) on the enum so views never contain `match` statements.

---

## 14. Observers

Location: `app/Observers`. Register with `#[ObservedBy]` on the model.

```php
class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->reference ??= Str::upper(Str::random(10));
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === OrderStatus::Shipped) {
            $order->customer->notify(new OrderShipped($order));
        }
    }
}
```

**Rules**

- Observers handle **side effects intrinsic to the model's lifecycle**: generating references, audit logs, cache busting, dispatching notifications/jobs.
- Do **not** put multi-step business logic in an observer; call an Action or dispatch a job.
- Use `ShouldHandleEventsAfterCommit` (or `$afterCommit = true`) when the side effect must happen only after the transaction commits.
- Keep observers fast; queue anything slow.
- Bulk `update()`/`delete()` queries do **not** fire observers; document it where relevant.

---

## 15. Rules

Location: `app/Rules`. Use `php artisan make:rule`.

```php
class ValidPhilippineMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^(09|\+639)\d{9}$/', (string) $value)) {
            $fail('validation.philippine_mobile')->translate();
        }
    }
}
```

**Rules**

- A rule validates **one concern** and is reusable across Requests.
- Rules needing data (the current user, a model) receive it through the constructor.
- Prefer built-in rules first; custom rules are for domain logic.
- Messages are translation keys.

---

## 16. Exports

Location: `app/Exports/{Domain}`. Uses `maatwebsite/excel`.

```php
class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return Order::query()->with('customer')->filter($this->filters);
    }

    public function headings(): array { return ['Reference', 'Customer', 'Status', 'Total']; }

    public function map($order): array
    {
        return [$order->reference, $order->customer->name, $order->status->label(), Money::format($order->total)];
    }
}
```

**Rules**

- Prefer `FromQuery` (chunked, memory-safe) over `FromCollection`.
- Large exports implement `ShouldQueue` and notify the user when ready.
- Map values explicitly; never dump raw models.
- Exports receive **filters**, not `Request` objects.
- Triggered from a dedicated invokable controller: `ExportOrdersController`.

---

## 17. API Resources

Location: `app/Http/Resources/{Domain}`.

```php
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'status'    => ['value' => $this->status->value, 'label' => $this->status->label()],
            'total'     => $this->total,
            'customer'  => CustomerResource::make($this->whenLoaded('customer')),
            'items'     => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'=> $this->created_at->toIso8601String(),
        ];
    }
}
```

**Rules**

- **Never return a model directly** from an API route.
- Use `whenLoaded()` for relations to prevent N+1.
- Dates are ISO-8601, money is integer minor units, enums expose `value` and `label`.
- Return `ResourceCollection` / `Resource::collection($paginator)` for lists so pagination meta is included.
- Version breaking changes under `Api/V2`.

---

## 18. Middleware

Location: `app/Http/Middleware` (the folder is singular in Laravel, even though shorthand may say "Middlewares"). Register in **`bootstrap/app.php`**.

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'   => \App\Http\Middleware\EnsureUserHasRole::class,
        'active' => \App\Http\Middleware\EnsureUserIsActive::class,
    ]);
    $middleware->web(append: [\App\Http\Middleware\SetLocale::class]);
})
```

**Rules**

- Middleware answers a **cross-cutting question** about a request: "is the user active?", "which locale?", "is this tenant valid?".
- Keep them small; delegate to a Service if logic grows.
- Parameterized middleware use `alias:param` (`role:admin,manager`).
- Never put authorization of a *specific model* in middleware; use Policies.

---

## 19. Notifications

Location: `app/Notifications/{Domain}`.

```php
class OrderShipped extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.order_shipped.subject', ['ref' => $this->order->reference]))
            ->markdown('notifications.order-shipped', ['order' => $this->order]);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return ['order_id' => $this->order->id, 'message' => __('notifications.order_shipped.body')];
    }
}
```

**Rules**

- Names are **past-tense events** (`OrderShipped`, `InvoicePaid`).
- Always `implements ShouldQueue`.
- Mail views live in `resources/views/notifications/` (or `emails/`) and use `x-mail::message`.
- Database notifications store **ids and message keys**, not rendered HTML.
- The in-app notification UI is rendered by `<x-tools.notification />` (see §23).

---

## 20. Views, View Components and Composers

### View Components (`app/View/Components`)

- **Class-based** components when there is logic, props validation, computed values or injected services.
- **Anonymous** components (`resources/views/components/**.blade.php`) only for trivial, logic-free markup.
- Every component in the library (`forms`, `tools`, `widgets`, `graphs`) is class-based so props are typed. See §23.

### View Composers (`app/View/Composers`)

Use composers to inject **shared data** into views (navigation, notification counts, current team).

```php
class NavigationComposer
{
    public function __construct(private readonly NavigationService $nav) {}

    public function compose(View $view): void
    {
        $view->with('navigation', $this->nav->forUser(auth()->user()));
    }
}
```

Register in `AppServiceProvider::boot()` (or a dedicated `ViewServiceProvider`):

```php
View::composer('partials.sidebar', NavigationComposer::class);
```

**Rules**

- Composers target **specific views**, not `*`, unless data is truly global.
- A composer may cache; it may not mutate state.
- Controller-specific data is passed by the controller, not a composer.

### Blade rules

- Use `{{ }}` (escaped). Use `{!! !!}` only for trusted, sanitized HTML, with a comment explaining why.
- Use `@props`, `{{ $attributes->merge([...]) }}`, `@class([])`, `@style([])`.
- No queries in Blade. No `@php` blocks that exceed two lines.
- Use `@forelse` with an empty state (`<x-widgets.empty-state />`).
- Use `@can`, `@auth`, `@guest` for authorization-based rendering.
- Translate all user-facing strings: `{{ __('orders.title') }}`.
- Use `@csrf` and `@method()` in every form (the `<x-tools.form>` component does it automatically).
- Use `@stack('scripts')` / `@push('scripts')` for page-level JS, never inline scripts in components.

---

## 21. Frontend Structure

### resources/views

```
views/
├── layouts/        app.blade.php, guest.blade.php, admin.blade.php
├── partials/       header, footer, sidebar, flash-messages
├── pages/          {feature}/{index,create,edit,show}.blade.php
├── components/     templates for class-based components (mirrors app/View/Components)
├── emails/
├── notifications/
└── errors/
```

- **Pages** extend a layout through the layout component: `<x-layouts.app title="Orders"> ... </x-layouts.app>`.
- CRUD pages use these exact names: `index`, `create`, `edit`, `show`; shared fields go in `_form.blade.php`.
- A **partial** is page-specific and not reusable; if it is reused, promote it to a component.

### resources/css

```
css/
├── app.css            @import "tailwindcss"; + @theme tokens + imports
├── base/              typography.css, forms.css (global element styles)
├── components/        only for third-party widgets that cannot be styled with utilities
└── themes/            dark.css, brand overrides
```

### resources/js

```
js/
├── app.js             import './bootstrap'; import Alpine ...; import components
├── bootstrap.js       axios defaults, CSRF header
├── components/        modal.js, dropdown.js, tabs.js, stepper.js, data-table.js, date-range.js
├── graphs/            chart-defaults.js, bar.js, line.js, pie.js
├── pages/             orders-index.js  (loaded only on that page)
└── utils/             dom.js, http.js, format.js
```

**Rules**

- **One entry** (`app.js`) for global behavior; **page entries** are loaded with `@vite` in the page and registered in `vite.config.js`.
- Component JS exports a **registration function** (`Alpine.data('modal', ...)`) or a **`data-*` initializer**. Never rely on global variables.
- Never write inline `<script>` in Blade components; push to a stack or import a module.
- JS reads configuration from `data-*` attributes or a JSON `<script type="application/json">` block, never string-interpolated PHP in JS.
- Use `fetch`/`axios` with the CSRF token configured in `bootstrap.js`.

### vite.config.js

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

Always load assets with `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

---

## 22. Tailwind CSS Rules and Principles

### Principles

1. **Utility-first.** Compose utilities in markup. Reach for `@apply` last.
2. **Components, not copy-paste.** Repeated utility clusters belong to a Blade component, not a CSS class.
3. **Design tokens in `@theme`.** Colors, fonts, spacing and radii are defined once in `app.css`.
4. **Mobile-first.** Unprefixed = mobile; add `sm: md: lg: xl:` to scale up.
5. **Semantic color names** (`primary`, `danger`) instead of raw palette names in components.
6. **Accessibility is not optional**: visible focus, contrast, `sr-only` labels, `aria-*`.

### Tokens (`resources/css/app.css`)

```css
@import "tailwindcss";

@source "../views/**/*.blade.php";
@source "../../app/View/Components/**/*.php";

@theme {
    --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

    --color-primary-50:  oklch(0.97 0.02 260);
    --color-primary-500: oklch(0.60 0.20 260);
    --color-primary-600: oklch(0.52 0.20 260);
    --color-primary-700: oklch(0.45 0.18 260);

    --color-success-600: oklch(0.60 0.15 150);
    --color-warning-600: oklch(0.70 0.16 80);
    --color-danger-600:  oklch(0.58 0.22 25);
    --color-info-600:    oklch(0.60 0.15 235);

    --radius-card: 0.75rem;
}

@custom-variant dark (&:where(.dark, .dark *));
```

### Class ordering (consistent, enforced by `prettier-plugin-tailwindcss`)

Layout → box model → typography → visuals → state/responsive. Do not hand-order; run the Prettier plugin.

### Rules

| Do                                                                 | Don't                                                            |
| ------------------------------------------------------------------ | ---------------------------------------------------------------- |
| Use token colors (`bg-primary-600`)                                | Use arbitrary values (`bg-[#3b82f6]`) except one-offs            |
| Use `gap-*` and `space-*` for spacing between siblings             | Use margins on every child                                       |
| Use `@class([...])` for conditional classes                        | Concatenate class strings in PHP with spaces everywhere          |
| Use `grid` / `flex` + `gap`                                        | Use floats or fixed pixel widths                                 |
| Use `focus-visible:ring-2 focus-visible:ring-primary-500`          | Remove outlines (`outline-none`) without a replacement           |
| Use `dark:` variants for every color utility in the library        | Ship components that break in dark mode                          |
| Use `@apply` for third-party HTML you cannot touch                 | Use `@apply` to recreate `.btn`, `.card` (use components instead)|
| Use `sr-only` for icon-only controls                               | Rely on icons alone for meaning                                  |

### Dynamic classes

Tailwind scans source files as plain text. **Never build class names by interpolation** (`"bg-{$color}-600"`). Map to full class strings:

```php
$classes = match ($this->variant) {
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white',
    'danger'  => 'bg-danger-600 hover:bg-danger-600/90 text-white',
    default   => 'bg-white hover:bg-gray-50 text-gray-900 ring-1 ring-gray-300',
};
```

### Responsive & layout conventions

- Page container: `mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8`.
- Vertical rhythm: `space-y-6` between page sections, `space-y-4` inside cards.
- Breakpoints used: `sm 640`, `md 768`, `lg 1024`, `xl 1280`. Do not add custom breakpoints without team agreement.
- Tables become horizontally scrollable on mobile (`overflow-x-auto`) or switch to cards (`<x-tools.small-table />`).

### Dark mode

Class-based (`.dark` on `<html>`). Persist the choice in `localStorage` through `resources/js/utils/theme.js`, and apply it before paint with a tiny inline script in the layout `<head>`.

---

## 23. Component Library

The UI library is **pure Laravel class-based Blade components**, organised into four families that mirror the original library layout:

| Family      | PHP namespace                  | View folder                       | Tag prefix        | Purpose                                     |
| ----------- | ------------------------------ | --------------------------------- | ----------------- | ------------------------------------------- |
| **Forms**   | `App\View\Components\Forms`    | `resources/views/components/forms`   | `<x-forms.*>`   | Inputs and form controls                    |
| **Tools**   | `App\View\Components\Tools`    | `resources/views/components/tools`   | `<x-tools.*>`   | Interactive/structural building blocks      |
| **Widgets** | `App\View\Components\Widgets`  | `resources/views/components/widgets` | `<x-widgets.*>` | Presentational display elements             |
| **Graphs**  | `App\View\Components\Graphs`   | `resources/views/components/graphs`  | `<x-graphs.*>`  | Chart wrappers                              |

### 23.1 Component inventory (mapping from the previous React library)

**Forms**

| Component class     | Tag                          | Notes                                              |
| ------------------- | ---------------------------- | -------------------------------------------------- |
| `Button`            | `<x-forms.button>`           | Variants: primary, secondary, danger, ghost; sizes; `loading` state; renders `<a>` when `href` is set |
| `Checkbox`          | `<x-forms.checkbox>`         |                                                    |
| `DateRange`         | `<x-forms.date-range>`       | Two dates, `name_from`/`name_to`; JS in `date-range.js` |
| `FieldGroup`        | `<x-forms.field-group>`      | Label + control slot + hint + error wrapper        |
| `Input`             | `<x-forms.input>`            | Base text-like input                               |
| `InputColor`        | `<x-forms.input-color>`      |                                                    |
| `InputDate`         | `<x-forms.input-date>`       |                                                    |
| `InputFile`         | `<x-forms.input-file>`       | Preview, size/type hints                           |
| `InputGroup`        | `<x-forms.input-group>`      | Input with prefix/suffix add-ons                   |
| `InputPassword`     | `<x-forms.input-password>`   | Show/hide toggle                                   |
| `InputRadio`        | `<x-forms.input-radio>`      | Supports a group via `options`                     |
| `InputSlider`       | `<x-forms.input-slider>`     | Range slider with live value                       |
| `InputSwitch`       | `<x-forms.input-switch>`     | Toggle                                             |
| `Select`, `Textarea`| `<x-forms.select>`, `<x-forms.textarea>` | Add alongside the above for completeness |

**Tools**

| Component class       | Tag                            | Notes                                           |
| --------------------- | ------------------------------ | ----------------------------------------------- |
| `ConfirmModal`        | `<x-tools.confirm-modal>`      | Wraps `Modal`, submits a form/route on confirm  |
| `DataTable`           | `<x-tools.data-table>`         | Columns, rows/paginator, sorting links, actions slot |
| `Dropdown`            | `<x-tools.dropdown>`           |                                                 |
| `Form`                | `<x-tools.form>`               | Handles `@csrf`, `@method`, `enctype`, error summary |
| `Modal`               | `<x-tools.modal>`              | Focus trap, `Esc` to close, named via `name`    |
| `Notification`        | `<x-tools.notification>`       | Toasts + flash messages                         |
| `NotificationLoading` | `<x-tools.notification-loading>` | Progress/pending toast (e.g., queued export)  |
| `SmallTable`          | `<x-tools.small-table>`        | Compact key/value or mobile table               |
| `Stepper`             | `<x-tools.stepper>`            | Multi-step wizard progress                      |
| `Tabs`                | `<x-tools.tabs>`               | Horizontal tabs                                 |
| `VerticalTabs`        | `<x-tools.vertical-tabs>`      | Sidebar-style tabs                              |

**Widgets**

| Component class | Tag                        | Notes                                        |
| --------------- | -------------------------- | -------------------------------------------- |
| `Accordion`     | `<x-widgets.accordion>`    |                                              |
| `Alert`         | `<x-widgets.alert>`        | `type`: success, info, warning, danger       |
| `Badge`         | `<x-widgets.badge>`        | Accepts an enum (`:status="$order->status"`) |
| `Callout`       | `<x-widgets.callout>`      |                                              |
| `Card`          | `<x-widgets.card>`         | Slots: `header`, default, `footer`           |
| `EmptyState`    | `<x-widgets.empty-state>`  | Icon, title, description, action slot        |
| `GraphCard`     | `<x-widgets.graph-card>`   | Card + title + a graph slot + period actions |
| `InfoBox`       | `<x-widgets.info-box>`     | KPI/stat tile                                |
| `Loading`       | `<x-widgets.loading>`      | Spinner/skeleton                             |
| `Logo`          | `<x-widgets.logo>`         | Brand mark, light/dark                       |

**Graphs**

| Component class       | Tag                              | Notes                                |
| --------------------- | -------------------------------- | ------------------------------------ |
| `Chart`               | `<x-graphs.chart>`               | **Base** component: all others extend/compose it |
| `BarChart`            | `<x-graphs.bar-chart>`           |                                      |
| `HorizontalBarChart`  | `<x-graphs.horizontal-bar-chart>`|                                      |
| `LineChart`           | `<x-graphs.line-chart>`          |                                      |
| `PieChart`            | `<x-graphs.pie-chart>`           |                                      |

### 23.2 Component rules

1. **Class-based, typed props.** Every component declares typed constructor properties with defaults. No untyped `@props` arrays for library components.
2. **One class + one view** per component, mirrored paths:
   `app/View/Components/Forms/Input.php` ↔ `resources/views/components/forms/input.blade.php`.
3. **Always merge attributes**: root element uses `{{ $attributes->merge(['class' => $classes]) }}` so callers can add classes, `x-data`, `data-*`, `wire`-free attributes.
4. **Never hard-code ids or names.** Accept `name`, derive `id` from `name` (or an explicit `id`) with `Str::slug`/`Str::uuid` fallback.
5. **Old input & errors are built in.** Form controls read `old($name, $value)` and `$errors->first($name)` themselves.
6. **Accessibility built in**: labels linked via `for`/`id`, `aria-invalid`, `aria-describedby` for hint/error, `role`/`aria-*` for modal, tabs, accordion and dropdown.
7. **Dark mode and focus styles** are part of the component, not left to the caller.
8. **Variants through `match`**, full class strings only (see §22).
9. **No queries or services in components** except via explicit constructor injection of a Service (e.g., a `Notification` component receiving the unread count). Prefer passing data in.
10. **Slots over booleans.** Use named slots (`<x-slot:header>`) instead of many `show_header` flags.
11. **JS is progressive.** Each interactive component works with a minimal `resources/js/components/{name}.js` module registered in `app.js`, driven by `data-component="modal"` or Alpine `x-data`. Server-rendered HTML must be meaningful before JS runs wherever possible.
12. **Components don't know pages.** No route names, no model-specific text hard-coded inside the library. Pass them in.
13. **Namespacing and discovery**: register the namespace in `AppServiceProvider::boot()` only if you move components outside `App\View\Components`.
14. **Every component has a Pest test** (renders, merges attributes, shows error state) and appears on the internal **`/styleguide`** page (local env only).
15. **Strings are translatable.** Default labels (e.g., "Cancel", "Confirm", "No data") come from `lang/`.

### 23.3 Generating components

```bash
php artisan make:component Forms/Input
php artisan make:component Forms/InputPassword
php artisan make:component Tools/Modal
php artisan make:component Widgets/Card
php artisan make:component Graphs/BarChart
```

This creates both the class and the view; **delete `render()` returning inline strings**, and keep view files under `resources/views/components/{family}`.

### 23.4 Reference implementations

#### Forms: `Button`

```php
<?php

declare(strict_types=1);

namespace App\View\Components\Forms;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    public function __construct(
        public string $variant = 'primary',   // primary|secondary|danger|ghost
        public string $size = 'md',           // sm|md|lg
        public string $type = 'button',
        public ?string $href = null,
        public bool $loading = false,
    ) {}

    public function classes(): string
    {
        $variant = match ($this->variant) {
            'primary'   => 'bg-primary-600 text-white hover:bg-primary-700 focus-visible:ring-primary-500',
            'secondary' => 'bg-white text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600',
            'danger'    => 'bg-danger-600 text-white hover:opacity-90 focus-visible:ring-danger-600',
            'ghost'     => 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800',
        };

        $size = match ($this->size) {
            'sm' => 'px-2.5 py-1.5 text-xs',
            'lg' => 'px-5 py-3 text-base',
            default => 'px-4 py-2 text-sm',
        };

        return "inline-flex items-center justify-center gap-2 rounded-md font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {$variant} {$size}";
    }

    public function render(): View
    {
        return view('components.forms.button');
    }
}
```

```blade
{{-- resources/views/components/forms/button.blade.php --}}
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes()]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $classes()]) }}
            @disabled($loading) @if($loading) aria-busy="true" @endif>
        @if ($loading) <x-widgets.loading size="sm" /> @endif
        {{ $slot }}
    </button>
@endif
```

Usage: `<x-forms.button variant="danger" type="submit">Delete</x-forms.button>`

#### Forms: `FieldGroup` + `Input`

```php
class Input extends Component
{
    public function __construct(
        public string $name,
        public string $type = 'text',
        public mixed $value = null,
        public ?string $id = null,
        public ?string $label = null,
        public ?string $hint = null,
    ) {
        $this->id ??= Str::slug($name, '_');
    }

    public function render(): View
    {
        return view('components.forms.input');
    }
}
```

```blade
{{-- resources/views/components/forms/input.blade.php --}}
<x-forms.field-group :for="$id" :label="$label" :hint="$hint" :error="$errors->first($name)">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'block w-full rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset',
            'bg-white text-gray-900 ring-gray-300 placeholder:text-gray-400',
            'dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600',
            'focus:ring-2 focus:ring-primary-500',
            'ring-danger-600' => $errors->has($name),
        ]) }}
    />
</x-forms.field-group>
```

Usage: `<x-forms.input name="email" type="email" label="Email" :value="$user->email" required />`

#### Tools: `Form`

```blade
{{-- resources/views/components/tools/form.blade.php --}}
<form method="{{ $spoofed ? 'POST' : $method }}" action="{{ $action }}"
      @if($files) enctype="multipart/form-data" @endif
      {{ $attributes->class('space-y-6') }}>
    @csrf
    @if ($spoofed) @method($method) @endif

    @if ($errors->any())
        <x-widgets.alert type="danger">{{ __('Please fix the errors below.') }}</x-widgets.alert>
    @endif

    {{ $slot }}
</form>
```

(`$spoofed` is computed in the class: `! in_array($method, ['GET', 'POST'])`.)

#### Tools: `Modal` (Alpine optional, progressive)

```blade
<div x-data="{ open: @js($show) }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title">
    <div class="absolute inset-0 bg-gray-900/50" x-on:click="open = false"></div>
    <div {{ $attributes->class(['relative w-full rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900', $maxWidthClass()]) }}>
        {{ $slot }}
    </div>
</div>
```

Open from anywhere: `<x-forms.button x-on:click="$dispatch('open-modal', 'delete-order')">Delete</x-forms.button>`

#### Tools: `DataTable`

```php
class DataTable extends Component
{
    /**
     * @param  array<int, array{key: string, label: string, sortable?: bool, class?: string}>  $columns
     */
    public function __construct(
        public array $columns,
        public LengthAwarePaginator|Collection $rows,
        public ?string $sort = null,
        public string $direction = 'asc',
        public ?string $emptyMessage = null,
    ) {}

    public function paginated(): bool
    {
        return $this->rows instanceof LengthAwarePaginator;
    }
}
```

```blade
<div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
    <table {{ $attributes->class('min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700') }}>
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                @foreach ($columns as $col)
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 {{ $col['class'] ?? '' }}">
                        @if ($col['sortable'] ?? false)
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $col['key'], 'direction' => $sort === $col['key'] && $direction === 'asc' ? 'desc' : 'asc']) }}">
                                {{ $col['label'] }}
                            </a>
                        @else
                            {{ $col['label'] }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($rows as $row)
                {{ $body($row) }}   {{-- scoped slot: pass a row renderer --}}
            @empty
                <tr><td colspan="{{ count($columns) }}">
                    <x-widgets.empty-state :title="$emptyMessage ?? __('No records found')" />
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($paginated()) <div class="mt-4">{{ $rows->links() }}</div> @endif
```

#### Widgets: `Badge` (enum-aware)

```php
class Badge extends Component
{
    public function __construct(
        public string $color = 'gray',
        public ?string $label = null,
        public BackedEnum|null $status = null,
    ) {
        if ($status && method_exists($status, 'color')) {
            $this->color = $status->color();
            $this->label ??= $status->label();
        }
    }

    public function classes(): string
    {
        return match ($this->color) {
            'success' => 'bg-success-600/10 text-success-600',
            'warning' => 'bg-warning-600/10 text-warning-600',
            'danger'  => 'bg-danger-600/10 text-danger-600',
            'info'    => 'bg-info-600/10 text-info-600',
            default   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
        };
    }
}
```

Usage: `<x-widgets.badge :status="$order->status" />`

#### Graphs: `Chart` base + `BarChart`

All charts render a `<canvas>` with a **JSON config in a data attribute**; one JS module (`resources/js/graphs/chart-defaults.js`) finds `[data-chart]` elements and initializes Chart.js. No inline scripts, no PHP-in-JS.

```php
class Chart extends Component
{
    /**
     * @param  array<int, string>  $labels
     * @param  array<int, array{label: string, data: array<int, int|float>, color?: string}>  $datasets
     */
    public function __construct(
        public string $type,                  // bar|line|pie|doughnut
        public array $labels,
        public array $datasets,
        public ?string $id = null,
        public string $height = 'h-72',
        public array $options = [],
    ) {
        $this->id ??= 'chart-' . Str::uuid()->toString();
    }

    /** @return array<string, mixed> */
    public function config(): array
    {
        return [
            'type'    => $this->type,
            'data'    => ['labels' => $this->labels, 'datasets' => $this->datasets],
            'options' => array_replace_recursive(['responsive' => true, 'maintainAspectRatio' => false], $this->options),
        ];
    }
}
```

```blade
{{-- components/graphs/chart.blade.php --}}
<div {{ $attributes->class(['relative w-full', $height]) }}>
    <canvas id="{{ $id }}" data-chart='@json($config())' role="img" aria-label="{{ $attributes->get('aria-label', 'Chart') }}"></canvas>
</div>
```

```php
class BarChart extends Chart
{
    public function __construct(array $labels, array $datasets, ?string $id = null, public bool $horizontal = false, array $options = [])
    {
        parent::__construct('bar', $labels, $datasets, $id, options: $horizontal ? ['indexAxis' => 'y'] + $options : $options);
    }
}
```

`HorizontalBarChart` is `BarChart` with `horizontal: true`; `LineChart` and `PieChart` follow the same pattern with their `type`.

Usage with a `GraphCard`:

```blade
<x-widgets.graph-card title="Monthly Sales">
    <x-graphs.bar-chart :labels="$months" :datasets="[['label' => 'Sales', 'data' => $totals]]" />
</x-widgets.graph-card>
```

**Graph rules**

- Data is prepared in a **Service/Action** (`SalesReportService::monthly()`) and passed in as arrays. Components never query.
- Colors come from CSS variables/tokens read in `chart-defaults.js`, so charts follow dark mode.
- Always give a text alternative (`aria-label`, or a visually-hidden table).
- Use `@vite` to load Chart.js only on pages that need it (`resources/js/pages/*`).

### 23.5 Usage example (page)

```blade
<x-layouts.app title="Orders">
    <x-widgets.card>
        <x-slot:header>
            <h1 class="text-lg font-semibold">{{ __('Orders') }}</h1>
            <x-forms.button :href="route('orders.create')">{{ __('New order') }}</x-forms.button>
        </x-slot:header>

        <x-tools.data-table :columns="$columns" :rows="$orders" :sort="request('sort')" :direction="request('direction', 'asc')">
            <x-slot:body :row="$order">
                <tr>
                    <td class="px-4 py-3">{{ $order->reference }}</td>
                    <td class="px-4 py-3"><x-widgets.badge :status="$order->status" /></td>
                </tr>
            </x-slot:body>
        </x-tools.data-table>
    </x-widgets.card>
</x-layouts.app>
```

---

## 24. Testing

- **Pest** with `RefreshDatabase` against a **MySQL test database** (`DB_DATABASE=app_testing`), not SQLite, so migrations, JSON columns and collations behave like production.
- Test layout mirrors `app/`: `tests/Unit/Actions`, `tests/Feature/Http/Controllers`, `tests/Feature/Components`.
- **Actions/Services:** unit/feature tests with real DB. Fake external boundaries (`Http::fake()`, `Notification::fake()`, `Queue::fake()`, `Storage::fake()`).
- **Controllers:** feature tests asserting status, redirects, view data, and database state.
- **Components:** `$this->blade('<x-forms.input name="email" />')->assertSee(...)`, or `$this->component(Input::class, [...])`.
- **Policies:** test each ability for each role.
- Use **factories** with states (`Order::factory()->shipped()->create()`).
- Name tests by behavior: `it('cancels a pending order and restocks items')`.
- CI must run Pint, Larastan, Pest and `npm run build`.

---

## 25. Security and Performance

**Security**

- CSRF on all state-changing web routes (the `@csrf` directive / `<x-tools.form>`).
- Escape output by default; sanitize rich text with a whitelist library before storing.
- Mass assignment protected via `$fillable`.
- Authorization on **every** controller action (Policies); never trust hidden form fields.
- Store uploaded files on a private disk; serve through signed/temporary URLs. Validate `mimes` **and** size.
- Rate-limit auth and API routes (`RateLimiter::for()` + `throttle:`).
- `.env` is never committed; `env()` is used only inside `config/*.php`.
- Hash passwords with the default `Hash` driver; never log secrets or PII.
- Set `APP_DEBUG=false` in production.

**Performance**

- Run in production: `php artisan config:cache route:cache view:cache event:cache` and `optimize`.
- Eager-load relations; paginate lists; select only needed columns on large tables.
- Cache expensive, rarely-changing reads (`Cache::remember`) with explicit keys and invalidation (in observers/actions).
- Queue anything slow (mail, exports, imports, webhooks, image processing).
- Use database indexes deliberately; review `EXPLAIN` for slow queries.
- Ship Vite production builds (hashed, minified); lazy-load page-specific JS.

---

## 26. Code Style and Tooling

- `declare(strict_types=1);` in every PHP file (not in Blade).
- **PSR-12 via Laravel Pint** (`./vendor/bin/pint`); run in CI and pre-commit.
- Type everything: parameter types, return types, property types. Use docblocks only for generics (`@param array<string, mixed>`).
- Prefer **constructor property promotion** and `readonly` where possible.
- Prefer **early returns** over nested conditionals; avoid `else` after `return`.
- Prefer **collections and Eloquent** over manual loops when it improves clarity.
- Use `match` over long `switch` / `if-elseif` chains.
- Comments explain **why**, not **what**.
- Frontend: Prettier + `prettier-plugin-tailwindcss`; ESLint for JS.
- Commit messages: Conventional Commits (`feat(orders): add cancel action`).
- Branches: `feature/…`, `fix/…`; every PR requires passing CI and one review.

---

## 27. Checklists

### New feature

- [ ] Migration (+ factory, + seeder if reference data)
- [ ] Model: fillable, `casts()`, relations, scopes, observer attribute
- [ ] Enum(s) for any status/type column
- [ ] Policy registered/auto-discovered
- [ ] Form Request(s) (Store / Update)
- [ ] Action(s) for each use case; Service if an integration is involved
- [ ] Controller (thin) + routes (named, grouped, middleware)
- [ ] Views under `resources/views/pages/{feature}`; reuse library components
- [ ] Lang entries added (no hard-coded strings)
- [ ] Notification / Export / Resource only if the feature needs them
- [ ] Tests: Action, Controller, Policy, any new Component
- [ ] Pint + Larastan + Pest green

### New UI component

- [ ] Class in `app/View/Components/{Family}` with typed props
- [ ] View in `resources/views/components/{family}`
- [ ] Merges `$attributes`; handles `old()`/`$errors` if a form control
- [ ] Full class strings (no interpolation); dark mode + focus styles
- [ ] ARIA/accessibility verified; keyboard operable
- [ ] JS module (only if needed) registered in `resources/js/app.js`
- [ ] Added to `/styleguide`; Pest render test written

### Before deploying

- [ ] `php artisan migrate --force`
- [ ] `npm run build`
- [ ] `php artisan optimize`
- [ ] Queue workers & scheduler running (`queue:work`, `schedule:run`)
- [ ] `APP_DEBUG=false`, correct `APP_URL`, cache/session/queue drivers set
