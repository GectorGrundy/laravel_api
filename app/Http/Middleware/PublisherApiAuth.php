<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PublisherProfile;
use App\Models\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PublisherApiDataContainer;

class PublisherApiAuth
{
    /**
     * @var PublisherApiDataContainer
     */
    private $data_container;

    public function __construct(PublisherApiDataContainer $data_container)
    {
        $this->data_container = $data_container;
    }

    public function handle(Request $request, \Closure $next)
    {
        if (!$request->filled('api_key')) {
            $this->returnBadAuth();
        }

        $publisher = PublisherProfile::with(['user'])->where('api_key', $request->input('api_key'))->first();
        if (is_null($publisher)) {
            $this->returnBadAuth();
        }

        $has_api_permission = UserPermission::userHasPermission($publisher['user_id'], UserPermission::API);
        if (!$has_api_permission) {
            $this->returnBadAuth();
        }

        $this->data_container->setPublisher($publisher->user);

        return $next($request);
    }

    private function returnBadAuth()
    {
        (new JsonResponse([
            'message' => trans('messages.unauthorized'),
            'status_code' => 403
        ], 403))
            ->send();
        exit;
    }
}
