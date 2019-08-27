<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class Timezones
{
    public const TZ_OFFSETS = 'timezones';
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request, \Closure $next)
    {
        $this->initTimezones();

        return $next($request);
    }

    public function initTimezones(): void
    {
        $this->container->singleton('timezones', function () {
            $app_tz = config('app.timezone');
            $user_tz = \Auth::user()['timezone'] ?? $app_tz;

            return [
                'app' => (new \DateTime('now', new \DateTimeZone(config('app.timezone'))))->format('P'),
                'user' => (new \DateTime('now', new \DateTimeZone($user_tz)))->format('P'),
            ];
        });
    }
}
