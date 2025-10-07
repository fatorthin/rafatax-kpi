<?php

namespace App\Providers;

use App\Models\Role as AppRole;
use App\Models\User as AppUser;
use App\Models\LogBook as AppLogBook;
use App\Models\ClientReport as AppClientReport;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\LogBookPolicy;
use App\Policies\ClientReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AppUser::class => UserPolicy::class,
        AppRole::class => RolePolicy::class,
        AppLogBook::class => LogBookPolicy::class,
        AppClientReport::class => ClientReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}


