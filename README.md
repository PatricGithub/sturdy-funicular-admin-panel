# WebWizr Admin Panel

A reusable Filament 3.x admin panel package for Laravel 11/12 projects. This package provides a complete admin panel with CRM, blog management, settings, and more.

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- Filament 3.x

## Installation

### 1. Add Repository to composer.json

Since this is a private package, add the GitHub repository to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:webwizr/admin-panel.git"
        }
    ]
}
```

### 2. Require the Package

```bash
composer require webwizr/admin-panel
```

### 3. Run the Install Command

```bash
php artisan webwizr:install
```

This command will:
- Publish the configuration file
- Run database migrations
- Create the initial admin user (interactive)

**Options:**
```bash
# Force run in production
php artisan webwizr:install --force

# Skip admin user seeding
php artisan webwizr:install --seed=false

# Provide admin credentials directly
php artisan webwizr:install --admin-email=admin@example.com --admin-password=secret --admin-name="Admin User"
```

### 4. Configure the Filament Panel

Create or update your `app/Providers/Filament/AdminPanelProvider.php`:

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use WebWizr\AdminPanel\AdminPanelServiceProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Apply package configuration
        $panel = AdminPanelServiceProvider::configurePanel($panel);

        return $panel
            ->default()
            // Add project-specific resources
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### 5. Configure Your User Model

Ensure your `User` model implements `FilamentUser` and has the required fields:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_super_admin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin || $this->email === 'admin@webwizr.eu';
    }
}
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=webwizr-admin-config
```

Edit `config/webwizr-admin.php`:

```php
return [
    // Panel settings
    'panel_id' => env('WEBWIZR_ADMIN_PANEL_ID', 'admin'),
    'panel_path' => env('WEBWIZR_ADMIN_PANEL_PATH', 'admin'),

    // Default admin credentials
    'admin' => [
        'name' => env('WEBWIZR_ADMIN_NAME', 'Admin'),
        'email' => env('WEBWIZR_ADMIN_EMAIL', 'admin@webwizr.eu'),
        'password' => env('WEBWIZR_ADMIN_PASSWORD', 'your-secure-password'),
    ],

    // Feature flags
    'features' => [
        'blog' => env('WEBWIZR_FEATURE_BLOG', true),
        'crm' => env('WEBWIZR_FEATURE_CRM', true),
        'chat_widget' => env('WEBWIZR_FEATURE_CHAT', true),
        'popup' => env('WEBWIZR_FEATURE_POPUP', true),
        'gdpr' => env('WEBWIZR_FEATURE_GDPR', true),
        'staging_comments' => env('WEBWIZR_FEATURE_STAGING', true),
    ],

    // Model overrides (use your own models)
    'models' => [
        'user' => null,      // Defaults to App\Models\User
        'language' => null,
        'customer' => null,
        'deal' => null,
        'blog_post' => null,
        'blog_category' => null,
    ],
];
```

## Extending the Package

### Override a Resource

Create a same-named class in your `app/Filament/Resources/` directory:

```php
<?php

namespace App\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\UserResource as BaseUserResource;

class UserResource extends BaseUserResource
{
    // Override methods as needed
    public static function form(Form $form): Form
    {
        $form = parent::form($form);

        // Add your customizations
        return $form;
    }
}
```

### Extend a Model

```php
<?php

namespace App\Models;

use WebWizr\AdminPanel\Models\Customer as BaseCustomer;

class Customer extends BaseCustomer
{
    // Add your customizations
    protected $appends = ['full_address'];

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->postal_code} {$this->city}";
    }
}
```

Then update the config:

```php
'models' => [
    'customer' => App\Models\Customer::class,
],
```

### Add Project-Specific Resources

Simply create new resources in your `app/Filament/Resources/` directory. They will be automatically discovered alongside the package resources.

## Included Features

### Filament Resources

- **UserResource** - User management with permissions
- **LanguageResource** - Multi-language support
- **BlogPostResource** - Blog post management
- **BlogCategoryResource** - Blog categories
- **CustomerResource** - CRM customer management
- **DealResource** - Sales pipeline deals
- **PipelineStageResource** - Pipeline stage configuration
- **TaskResource** - Task management
- **CallbackResource** - Callback scheduling
- **ContactInquiryResource** - Contact form submissions
- **StagingCommentResource** - Staging review comments

### Filament Pages

- **Functionality** - Widget settings (chat, popup, GDPR, buttons)
- **FeatureSettings** - Feature flag management
- **WebsiteAppearance** - Site appearance settings
- **LeadsPipeline** - CRM pipeline overview

### Widgets

- **PersonalHotlistWidget** - Personal hotlist dashboard widget

## Migrations

The package includes all necessary migrations for:
- User extensions (is_admin, is_super_admin)
- Languages and translations
- Site settings (popup, chat, GDPR, buttons)
- Blog system (posts, categories, tags)
- CRM (customers, deals, pipeline stages, tasks, callbacks)
- Permissions system
- Contact inquiries

## Seeders

Run the package seeders manually if needed:

```bash
php artisan db:seed --class="WebWizr\\AdminPanel\\Database\\Seeders\\AdminUserSeeder"
php artisan db:seed --class="WebWizr\\AdminPanel\\Database\\Seeders\\DefaultSettingsSeeder"
php artisan db:seed --class="WebWizr\\AdminPanel\\Database\\Seeders\\PermissionsSeeder"
```

## Environment Variables

```env
# Panel Configuration
WEBWIZR_ADMIN_PANEL_ID=admin
WEBWIZR_ADMIN_PANEL_PATH=admin

# Admin User (for installation)
WEBWIZR_ADMIN_NAME=Admin
WEBWIZR_ADMIN_EMAIL=admin@example.com
WEBWIZR_ADMIN_PASSWORD=your-secure-password

# Feature Flags
WEBWIZR_FEATURE_BLOG=true
WEBWIZR_FEATURE_CRM=true
WEBWIZR_FEATURE_CHAT=true
WEBWIZR_FEATURE_POPUP=true
WEBWIZR_FEATURE_GDPR=true
WEBWIZR_FEATURE_STAGING=true
```

## License

Proprietary - WebWizr Internal Use Only
