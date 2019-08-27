<?php
declare(strict_types=1);

namespace App\Http\GoSanitizers;

use Illuminate\Http\Request;

class PhoneSanitizer
{
    public function __construct(Request $request)
    {
        $request->merge([
            'phone' => mb_substr($request->input('phone', ''), 0, 255)
        ]);
    }
}