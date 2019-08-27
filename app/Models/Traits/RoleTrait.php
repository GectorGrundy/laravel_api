<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Scopes\RoleScope;

trait RoleTrait
{
    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootRoleTrait()
    {
        static::addGlobalScope(new RoleScope);
    }


    public function getQualifiedRoleColumn()
    {
        return implode('.', [$this->getTable(), 'role']);
    }

}