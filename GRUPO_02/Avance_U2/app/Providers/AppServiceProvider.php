<?php

namespace App\Providers;

use App\Contracts\LlmClient;
use App\Models\BaseConocimiento;
use App\Models\EvaluacionRag;
use App\Models\Ticket;
use App\Policies\BaseConocimientoPolicy;
use App\Policies\EvaluacionRagPolicy;
use App\Policies\TicketPolicy;
use App\Services\Llm\GeminiClient;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LlmClient::class, GeminiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(BaseConocimiento::class, BaseConocimientoPolicy::class);
        Gate::policy(EvaluacionRag::class, EvaluacionRagPolicy::class);
    }
}
