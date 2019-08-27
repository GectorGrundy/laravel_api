<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\GoDataContainer;
use App\Models\Traits\StaticFileValidator;
use App\Services\Cloaking\Parser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Проверка на fallback запрос от static nginx location
 */
class NginxForStatic
{
    use StaticFileValidator;

    /**
     * @var GoDataContainer
     */
    private $data_container;

    public function __construct(GoDataContainer $data_container)
    {
        $this->data_container = $data_container;
    }

    public function handle(Request $request, \Closure $next)
    {
        $path = $request->getRequestUri();
        if (!$this->isStaticFile($path)) {
            return $next($request);
        }

        $current_domain = $this->data_container->getCurrentDomain();
        if ($current_domain->isCloaked()) {
            /**
             * @var Parser $parser
             */
            $parser = app(Parser::class);
            $parser->configure([
                'donor_charset' => $current_domain->donor_charset,
                'donor_url' => $current_domain->donor_url,
                'current_domain' => $current_domain->host,
            ]);
            /**
             * @var Response $response
             */
            $response = $parser->parse($path);

            foreach ($response->headers->all() as $header => $value) {
                header(ucfirst($header) . ': ' . implode(',', $value));
            }

            header('Content-Type: ' . $this->getContentType($path));
            header('Cache-Control: public, max-age=31536000');

            echo $response->getContent();
            die;
        }

        abort(404);
    }
}
