<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\RoleTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this search(?string $search_field, ?string $search)
 * @property AdministratorProfile profile
 */
class Administrator extends User
{
    use RoleTrait;

    public $userRole = 'administrator';

    public function profile()
    {
        return $this->hasOne(AdministratorProfile::class, 'user_id');
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        if (empty($search_field) || empty($search)) {
            return $builder;
        }

        $search = urldecode($search);

        switch ($search_field) {
            case 'email':
                /**
                 * @var UserElasticQueries $queries
                 */
                $queries = app(UserElasticQueries::class);
                $user_ids = $queries->findIdsByEmail($search);

                return $builder->whereIn('id', $user_ids);
        }

        return $builder;
    }
}
