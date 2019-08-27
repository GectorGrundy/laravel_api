<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Exceptions\Cloaking\DifferentHosts;
use App\Http\Controllers\Controller;

class ErrorsController extends Controller
{
    public function cloaking()
    {
        throw new DifferentHosts();
    }
}
