<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Classes\GeoInspector;
use App\Classes\IpInspector;
use App\Exceptions\Hashids\NotDecodedHashException;
use App\Exceptions\Visitor\DoNotExistsSid;
use App\Http\GoDataContainer;
use App\Http\Requests\Go\GoRequest;
use App\Models\Visitor;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VisitorInfo
{
    private $go_request;
    private $visitor;
    private $geo_inspector;
    private $data_container;
    private $ip_inspector;

    public function __construct(
        GoRequest $go_request, Visitor $visitor, GeoInspector $geo_inspector, GoDataContainer $data_container,
        IpInspector $ip_inspector
    )
    {
        $this->go_request = $go_request;
        $this->visitor = $visitor;
        $this->geo_inspector = $geo_inspector;
        $this->data_container = $data_container;
        $this->ip_inspector = $ip_inspector;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $s_id = $this->getSid($request);
            $visitor_info = $this->visitor->getInfoBySessionId($s_id);
        } catch (DoNotExistsSid | ModelNotFoundException | NotDecodedHashException $e) {
            $s_id = $this->visitor->createNew([])['session_id'];
            $visitor_info = [];
        }

        $visitor_ip = $this->getIp();

        $this->data_container->setVisitor([
            's_id' => $s_id,
            'info' => $visitor_info,
            'is_fallback' => count($visitor_info) < 1,
            'ip' => $visitor_ip,
            'ips' => $this->go_request->getIps(),
            'user_agent' => $request->header('User-Agent'),
            'is_mobile' => $this->go_request->getIsMobile(),
            'browser_locale' => mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2),
            'geo_ids' => $this->geo_inspector->getGeoIds($visitor_ip),
            'referer' => mb_substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255),
        ]);

        return $next($request);
    }

    private function getSid(Request $request): string
    {
        if ($request->filled('s_id')) {
            return $request->get('s_id');
        }

        if ($request->hasCookie('s_id')) {
            return $request->cookie('s_id');
        }

        throw new DoNotExistsSid();
    }

    /**
     * Получение ip адреса посетителя. Если это локальная разработка - возврат явно указанного ip-адресса
     *
     * @return string
     */
    public function getIp(): string
    {
        if (config('env.replace_my_ip', false)) {
            return '212.90.59.69';
        }

        return $this->ip_inspector->getIp();
    }
}
