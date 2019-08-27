<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;

class SystemController extends Controller
{
    use Helpers;

    public function getLandingsPath()
    {
        return [
            'response' => config('env.landings_path'),
            'status_code' => 200
        ];
    }

}
