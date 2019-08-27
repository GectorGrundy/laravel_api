<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserUserPermission;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\UserUserPermission as R;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserUserPermissionsController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /user_user_permissions.getForUser user_user_permissions.getForUser
     * @apiGroup UserPermission
     * @apiPermission admin
     * @apiPermission support
     * @apiParam {String} user_hash
     * @apiSampleRequest /user_user_permissions.getForUser
     */
    public function getForUser(R\GetForUserRequest $request)
    {
        try {
            $user = User::where('hash', $request->input('user_hash'))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->response->error(trans('validation.exists', ['attribute' => 'user_hash']), 404);
        }

        $permission_titles = UserPermission::getForUser($user);

        $permissions = UserPermission::whereIn('title', $permission_titles)->get();

        return [
            'response' => $permissions,
            'status_code' => 200,
        ];
    }

    /**
     * @api {POST} /user_user_permissions.sync user_user_permissions.sync
     * @apiGroup UserPermission
     * @apiPermission admin
     * @apiPermission support
     * @apiParam {String} user_hash
     * @apiParam {Array} permissions[] IDs of user permissions
     * @apiSampleRequest /user_user_permissions.sync
     */
    public function sync(R\SyncRequest $request)
    {
        $permissions = $request->input('permissions', []);
        $user_id = \Hashids::decode($request->input('user_hash'))[0] ?? 0;

        if (!$user_id) {
            return $this->response->error(trans('validation.exists', ['attribute' => 'user_hash']), 404);
        }

        try {
            // Validate possibility to process this user and cant set to himself
            User::where('id', $user_id)
                ->where('id', '!=', \Auth::user()['id'])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->response->error(trans('validation.exists', ['attribute' => 'user_hash']), 404);
        }

        UserUserPermission::where('user_id', $user_id)->delete();

        // Добавляются права доступа, которые работают по принципу "Включение(INCLUDING)"
        foreach ($permissions as $permission_id) {

            $permission = UserPermission::find($permission_id);

            if ($permission->toggle_type === UserPermission::INCLUDING) {
                UserUserPermission::create([
                    'user_id' => $user_id,
                    'user_permission_id' => $permission_id
                ]);
            }
        }

        // Запрещаются права, которые работают по принципу "Исключение(INCLUDING)",
        // так как они не могут быть переданы при запросе
        $permissons = UserPermission::toggleType(UserPermission::EXCLUDING)->whereNotIn('id', $permissions)->get();

        $permissons->each(function ($permission) use ($user_id) {
            UserUserPermission::create([
                'user_id' => $user_id,
                'user_permission_id' => $permission->id
            ]);
        });

        UserPermission::flushCache($user_id);

        return [
            'status_code' => 202,
            'message' => trans('user_user_permissions.on_sync_success')
        ];
    }
}
