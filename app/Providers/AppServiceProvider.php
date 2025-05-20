<?php

namespace App\Providers;

use App\Models\DeliveryBooking;
use App\Models\PaymentPlan;
use Illuminate\Support\ServiceProvider;
use App\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Profile;
use App\Models\SMSTopUp;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Relation::morphMap([
            'aprofile' => Profile::class,
            'sms_topup' => SMSTopUp::class,
            'booking' => DeliveryBooking::class,
            'subscription' => PaymentPlan::class
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page'): LengthAwarePaginator {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            $path = (!empty(request()->query())) ?
                LengthAwarePaginator::resolveCurrentPath() . '?' . http_build_query(request()->query()) :
                LengthAwarePaginator::resolveCurrentPath();

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage)->values(),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => $path,
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
