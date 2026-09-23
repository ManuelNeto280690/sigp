<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionHelper;
use App\Helpers\ConfigHelper;
use Carbon\Carbon;
use App\Models\MovimentoCarga;
use App\Observers\MovimentoCargaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar o ConfigHelper como singleton
        $this->app->singleton('config.helper', function () {
            return new \App\Helpers\ConfigHelper();
        });

        // Registrar a facade AppConfig
        $this->app->bind('app.config', function () {
            return new \App\Helpers\ConfigHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar diretivas Blade para permissions
        Blade::if('can', function ($permission) {
            return PermissionHelper::can($permission);
        });
        
        Blade::if('canAny', function ($permissions) {
            return PermissionHelper::canAny($permissions);
        });
        
        Blade::if('hasRole', function ($role) {
            return PermissionHelper::hasRole($role);
        });
        
        Blade::if('hasAnyRole', function ($roles) {
            return PermissionHelper::hasAnyRole($roles);
        });
        
        Blade::if('isAntaq', function () {
            return PermissionHelper::isAntaq();
        });
        
        Blade::if('isConcessionaria', function () {
            return PermissionHelper::isConcessionaria();
        });

        // Registrar diretivas Blade para configurações
        Blade::directive('config', function ($expression) {
            return "<?php echo ConfigHelper::get($expression); ?>";
        });

        // Compartilhar configurações globalmente com todas as views
        View::composer('*', function ($view) {
            $view->with('appConfigs', ConfigHelper::all());
        });

        MovimentoCarga::observe(MovimentoCargaObserver::class);
        Carbon::setLocale('pt_BR');
    }
}
