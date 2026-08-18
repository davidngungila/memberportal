<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\GoogleSheetRepositoryInterface;
use App\Models\User;
use App\Policies\DepositPolicy;
use App\Policies\InvestmentPolicy;
use App\Policies\LoanPolicy;
use App\Policies\MemberPolicy;
use App\Policies\SavingsPolicy;
use App\Policies\SwfPolicy;
use App\Policies\UserPolicy;
use App\Repositories\GoogleSheetRepository;
use App\Services\EncryptedIdService;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleSheetRepositoryInterface::class, GoogleSheetRepository::class);
        $this->app->singleton(EncryptedIdService::class);
        $this->app->singleton(MailConfigService::class);
    }

    public function boot(): void
    {
        Gate::policy('member', MemberPolicy::class);
        Gate::policy('loan', LoanPolicy::class);
        Gate::policy('savings', SavingsPolicy::class);
        Gate::policy('deposit', DepositPolicy::class);
        Gate::policy('swf', SwfPolicy::class);
        Gate::policy('investment', InvestmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('admin-only', function (User $user): bool {
            return $user->isAdmin();
        });

        Gate::define('member-only', function (User $user): bool {
            return $user->isMember();
        });

        Gate::define('view-member-data', function (User $user, string $memberNumber): bool {
            return $user->isAdmin() || $user->member_number === $memberNumber;
        });

        // Blade directive to encrypt IDs
        Blade::directive('encryptId', function ($expression) {
            return "<?php echo app(\App\Services\EncryptedIdService::class)->encrypt({$expression}); ?>";
        });

        View::composer('layouts.registration', function ($view) {
            $user = auth()->user();
            $helper = app(\App\Services\Registration\RegistrationViewHelper::class);

            if ($user) {
                $application = $user->membershipApplications()
                    ->whereIn('application_status', ['draft', 'in_progress', 'correction_required'])
                    ->latest()
                    ->first();

                $helper->setApplication($application);
            }

            $progress = $helper->getProgress();
            $application = $helper->getApplication();

            $view->with(compact('application', 'progress'));
        });
    }
}
