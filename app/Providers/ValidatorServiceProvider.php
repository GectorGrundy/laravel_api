<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Validator;

class ValidatorServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        $this->addJsonObjectRule();
        $this->addColorRule();
    }

    public function register()
    {

    }

    private function addJsonObjectRule()
    {
        Validator::extend('json_object', function ($attribute, $value, $parameters, $validator) {

            if (!is_string($value) || !starts_with($value, '{')) {
                return false;
            }

            if (!is_scalar($value) && !method_exists($value, '__toString')) {
                return false;
            }

            json_decode($value);

            return json_last_error() === JSON_ERROR_NONE;
        });
    }

    private function addColorRule()
    {
        Validator::extend('color', function ($attribute, $value, $parameters, $validator) {

            if (!is_string($value)) {
                return false;
            }

            $strlen = strlen($value);

            return $strlen === 3 || $strlen === 6;
        });
    }
}