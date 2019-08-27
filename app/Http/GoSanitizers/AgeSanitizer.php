<?php
declare(strict_types=1);

namespace App\Http\GoSanitizers;

use Illuminate\Http\Request;

class AgeSanitizer
{
    public function __construct(Request $request)
    {
        $request->merge([
            'age' => (int)mb_substr($request->input('age', ''), 0, 255)
        ]);
    }
}