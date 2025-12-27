<?php

namespace WebWizr\AdminPanel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class InstallCommand extends Command
{
    protected $signature = 'webwizr:install
                            {--force : Force the operation to run when in production}
                            {--seed : Run the database seeders}
                            {--admin-email= : The admin user email}
                            {--admin-password= : The admin user password}
                            {--admin-name= : The admin user name}';

    protected $description = 'Install the WebWizr Admin Panel package';

    public function handle(): int
    {
        $this->info('Installing WebWizr Admin Panel...');

        // Step 1: Publish config
        $this->publishConfig();

        // Step 2: Run migrations
        $this->runMigrations();

        // Step 3: Seed admin user
        if ($this->option('seed') || $this->confirm('Do you want to seed the admin user?', true)) {
            $this->seedAdminUser();
        }

        // Step 4: Instructions for panel setup
        $this->showPanelSetupInstructions();

        $this->newLine();
        $this->info('WebWizr Admin Panel installed successfully!');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->info('Publishing configuration...');

        $this->call('vendor:publish', [
            '--tag' => 'webwizr-admin-config',
            '--force' => $this->option('force'),
        ]);
    }

    protected function runMigrations(): void
    {
        $this->info('Running migrations...');

        $this->call('migrate', [
            '--force' => $this->option('force'),
        ]);
    }

    protected function seedAdminUser(): void
    {
        $this->info('Creating admin user...');

        $config = config('webwizr-admin.admin');

        $email = $this->option('admin-email')
            ?? env('WEBWIZR_ADMIN_EMAIL')
            ?? $this->ask('Admin email', $config['email']);

        $name = $this->option('admin-name')
            ?? env('WEBWIZR_ADMIN_NAME')
            ?? $this->ask('Admin name', $config['name']);

        $password = $this->option('admin-password')
            ?? env('WEBWIZR_ADMIN_PASSWORD')
            ?? $this->secret('Admin password (leave empty for default)');

        if (empty($password)) {
            $password = $config['password'];
        }

        $userModel = config('webwizr-admin.models.user') ?? 'App\\Models\\User';

        if (!class_exists($userModel)) {
            $this->error("User model {$userModel} not found.");
            return;
        }

        $user = $userModel::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info("Admin user created: {$email}");
        } else {
            $this->info("Admin user already exists: {$email}");
        }
    }

    protected function showPanelSetupInstructions(): void
    {
        $this->newLine();
        $this->info('Next Steps:');
        $this->newLine();
        $this->line('1. Create or update your AdminPanelProvider at app/Providers/Filament/AdminPanelProvider.php:');
        $this->newLine();

        $code = <<<'PHP'
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
        $panel = AdminPanelServiceProvider::configurePanel($panel);

        return $panel
            ->default()
            // Add project-specific resources here
            // ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
PHP;

        $this->line($code);

        $this->newLine();
        $this->line('2. Ensure your User model implements FilamentUser and has is_admin field:');
        $this->newLine();

        $userCode = <<<'PHP'
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    protected $fillable = ['name', 'email', 'password', 'is_admin'];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            // ...
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
PHP;

        $this->line($userCode);

        $this->newLine();
        $this->line('3. Access your admin panel at: ' . url(config('webwizr-admin.panel_path', 'admin')));
    }
}
