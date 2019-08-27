<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Classes\IpInspector;
use Closure;
use App\Models\PostbackinLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostbackinCreateLog
{
    /**
     * @var IpInspector
     */
    private $ip_inspector;

    public function __construct(IpInspector $ip_inspector)
    {
        $this->ip_inspector = $ip_inspector;
    }

    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        PostbackinLog::create([
            'api_key' => $request->input('api_key', ''),
            'lead_id' => $request->input('lead')->id ?? 0,
            'request' => json_encode($request->except(['lead', 'api_key'])),
            'ip' => $this->ip_inspector->getIp(),
            'response_code' => $response->getStatusCode(),
            'response' => $response->getContent() ?? '',
        ]);

        return $response;
    }
}
