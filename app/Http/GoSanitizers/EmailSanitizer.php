<?php
declare(strict_types=1);

namespace App\Http\GoSanitizers;

use Illuminate\Http\Request;

class EmailSanitizer
{
    public function __construct(Request $request)
    {
        $request->merge([
            'email' => mb_substr($request->input('email', ''), 0, 255)
        ]);
    }
}