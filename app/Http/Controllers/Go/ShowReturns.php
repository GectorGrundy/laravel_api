<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Classes\LandingHandler;
use App\Http\Controllers\Controller;
use App\Http\GoDataContainer;
use App\Services\LandingUrlResolver;
use App\Support\LandingFileCompiler;

class ShowReturns extends Controller
{
    public function __invoke(LandingFileCompiler $compiler, GoDataContainer $data_container, LandingHandler $handler)
    {
        $html = $compiler->compile($data_container->getSite()->domain, LandingUrlResolver::RETURNS);

        $html = str_replace(['<head>'], ['<head>' . $handler->getBaseTag()], $html);

        return response($html);
    }
}
