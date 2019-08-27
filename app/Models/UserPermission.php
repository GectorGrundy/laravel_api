<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends AbstractEntity
{
    public const API = 'API';
    public const FLOW_CUSTOM_CODE = 'FLOW_CUSTOM_CODE';
    public const CLOAKING = 'CLOAKING';

    public const EXCLUDING = 'excluding';
    public const INCLUDING = 'including';

    public const GET_FOR_USER_CACHE_KEY = 'UserPermission::getForUser';

    protected $fillable = ['title', 'description', 'toggle_type', 'user_role'];
    protected $hidden = ['created_at', 'updated_at'];

    public function scopeToggleType(Builder $builder, string $toggle_type)
    {
        return $builder->where('toggle_type', $toggle_type);
    }

    public static function userHasPermission(int $user_id, string $user_permission_title): bool
    {
        $user_permission_info = self::where('title', $user_permission_title)->firstOrFail();

        switch ($user_permission_info->toggle_type) {
            case self::EXCLUDING:
                return !self::userUserPermissionExists($user_id, $user_permission_info['id']);

            case self::INCLUDING:
                return self::userUserPermissionExists($user_id, $user_permission_info['id']);

            default:
                throw new \LogicException('Incorrect type of User Permission');
        }
    }

    public static function userUserPermissionExists(int $user_id, int $user_permission_id): bool
    {
        return DB::table('user_user_permission')
            ->where('user_id', $user_id)
            ->where('user_permission_id', $user_permission_id)
            ->exists();
    }

    public static function getForUser(User $user)
    {
        $key = self::getCacheKey($user['id']);

        $titles = \Cache::get($key, function () use ($user, $key) {
            $permissions = DB::select("SELECT `id`, `title`
                  FROM `user_permissions` as `up`
                  WHERE `toggle_type` = '" . self::INCLUDING . "' AND EXISTS (
                      SELECT `id` 
                      FROM `user_user_permission` 
                      WHERE `user_permission_id` = `up`.`id` 
                      AND `user_id` = ?
                    )
                  OR `toggle_type` = '" . self::EXCLUDING . "' AND NOT EXISTS (
                      SELECT `id` 
                      FROM `user_user_permission` 
                      WHERE `user_permission_id` = `up`.`id` 
                      AND `user_id` = ?
                    )",
                [$user['id'], $user['id']]
            );

            $titles = collect($permissions)->pluck('title')->toArray();

            \Cache::forever($key, $titles);

            return $titles;
        });

        return $titles;
    }

    public static function flushCache(int $user_id)
    {
        \Cache::forget(self::getCacheKey($user_id));
    }

    private static function getCacheKey($user_id)
    {
        return self::GET_FOR_USER_CACHE_KEY . ':' . $user_id;
    }
}
