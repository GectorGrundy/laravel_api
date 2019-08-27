<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class CreateApiLog
{
    private static $excepted_api_methods = [
        'auth.getUser', 'user.getSourceList', 'user_permission.getUserPermissions', 'locale.getList', 'file.show'
    ];

    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $api_method = substr($request->getPathInfo(), 1);

        if (\in_array($api_method, self::$excepted_api_methods) || \is_null($response->getContent())) {
            return $response;
        }

        $response_content = new \stdClass();
        $request_method = $request->getMethod();

        if ($request_method !== 'GET') {
            $response_content = json_decode($response->getContent(), true)['response'] ?? new \stdClass();
        }

        ApiLog::create([
            'user_id' => auth()->id(),
            'admin_id' => $this->getAdminId($request),
            'request_method' => $request_method,
            'api_method' => $api_method,
            'request' => json_encode($this->getRequestParams($request)),
            'response_code' => $response->getStatusCode(),
            'response' => json_encode($response_content),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $this->getIp($request),
        ]);

        return $response;
    }

    private function getRequestParams(Request $request): array
    {
        try {
            return $request->except(['request_user', 'token', 'locale_info', 'auth_token', 'preview']);
        } catch (FileNotFoundException $e) {
            return [];
        }
    }

    private function getIp(Request $request)
    {
        return $request->hasHeader('ip') ? $request->header('ip') : $request->ip();
    }

    private function getAdminId(Request $request)
    {
        $foreign_user_hash = $request->input('request_user')['payload']['foreign_user_hash'] ?? 0;

        return \Hashids::decode($foreign_user_hash)[0] ?? 0;
    }

}
