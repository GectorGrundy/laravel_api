<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Routing\Router;
use Dingo\Api\Routing\Router as DingoRouter;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    private $router;

    protected $api_namescape = 'App\Http\Controllers';
    protected $go_namescape = 'App\Http\Controllers\Go';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        $this->router = $this->app['router'];

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param DingoRouter $dingo_router
     */
    public function map(DingoRouter $dingo_router)
    {
        if (isApiRequest()) {
            $this->mapApiRoutes($dingo_router);
        } else {
            $this->mapGoRoutes($this->router);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * @param DingoRouter $router
     */
    protected function mapApiRoutes(DingoRouter $router)
    {
        $router->group([
            'version' => 'v1',
            'namespace' => $this->api_namescape,
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "go" routes for the application.
     *
     * @param Router $router
     */
    protected function mapGoRoutes(Router $router)
    {
        $router->group([
            'namespace' => $this->go_namescape,
            'middleware' => ['cookie']
        ], function ($router) {
            require base_path('routes/go.php');
        });
    }
}