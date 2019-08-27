<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Http\Controllers\Controller;

class RedirectToExternalLanding extends Controller
{
    public function __invoke(?string $clickid)
    {
        if (!request()->filled('to')) {
            return abort(404);
        }

        return redirect()
            ->away(request('to'))
            ->withCookie(cookie('clickid', $clickid, '.' . request()->server('HTTP_HOST')));
    }
}
