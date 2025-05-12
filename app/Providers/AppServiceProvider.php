<?php

namespace App\Providers;

use App\Models\AgendaFixa;
use App\Models\Curso;
use App\Models\Turma;
use App\Policies\AgendaFixaPolicy;
use App\Policies\CursoPolicy;
use App\Policies\TurmaPolicy;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    protected $policies = [
        Curso::class => CursoPolicy::class,
        Turma::class => TurmaPolicy::class,
        AgendaFixa::class => AgendaFixaPolicy::class
    ];
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
