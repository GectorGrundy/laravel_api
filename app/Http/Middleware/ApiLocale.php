<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\Locale as LocaleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ApiLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale_code = $request->input('locale');

        if (!empty($locale_code)) {

            try {
                $locale_info = LocaleModel::getByCode($locale_code);

                app()->setLocale($locale_info['code']);

                $request->merge(['locale_info' => $locale_info]);
            } catch (ModelNotFoundException $e) {

            }
        }

        return $next($request);
    }
}
