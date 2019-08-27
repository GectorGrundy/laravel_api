<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\UserPermission;

class UserPermissionsController extends Controller
{
    use Helpers;

    public function getList()
    {
        $permissions = UserPermission::all();

        return [
            'status_code' => 200,
            'response' => $permissions,
        ];
    }
}
