<?php
declare(strict_types=1);

namespace App\Http\GoSanitizers;

use Illuminate\Http\Request;

class NameSanitizer
{
    public function __construct(Request $request)
    {
        $request->merge([
            'client' => mb_substr($request->input('client', ''), 0, 255)
        ]);
    }
}