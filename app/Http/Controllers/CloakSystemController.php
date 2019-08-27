<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\CloakSystem;

class CloakSystemController extends Controller
{
    use Helpers;

    public function getList()
    {
        $cloak_systems = CloakSystem::withoutTest()->get();

        return $this->response->array(['response' => $cloak_systems, 'status_code' => 200]);
    }
}
