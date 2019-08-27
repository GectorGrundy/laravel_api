<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\MoneyManager;
use App\Models\Traits\RoleTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this search(?string $search_field, ?string $search)
 */
class Publisher extends User
{
    use RoleTrait;

    public $userRole = 'publisher';

    public function profile()
    {
        return $this->hasOne(PublisherProfile::class, 'user_id');
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        if (empty($search_field) || empty($search)) {
            return $builder;
        }

        $search = urldecode($search);

        switch ($search_field) {
            case 'id':
                return $builder->where('users.id', $search);

            case 'hash':
                return $builder->where('users.hash', $search);

            case 'email':
                /**
                 * @var UserElasticQueries $queries
                 */
                $queries = app(UserElasticQueries::class);
                $user_ids = $queries->findIdsByEmail($search);

                return $builder->whereIn('users.id', $user_ids);

            case 'skype':
                return $builder->whereHas('profile', function (Builder $builder) use ($search) {
                    return $builder->where('skype', $search);
                });

            case 'telegram':
                return $builder->whereHas('profile', function (Builder $builder) use ($search) {
                    return $builder->where('telegram', $search);
                });

            case 'phone':
                return $builder->whereHas('profile', function (Builder $builder) use ($search) {
                    return $builder->where('phone', $search);
                });

            case 'balance':
                return $builder->whereHas('profile', function (Builder $builder) use ($search) {
                    $field = PublisherProfile::getBalanceFieldByCurrencyId((int)request('currency_id'));
                    $operator = request('constraint') === 'less' ? '<' : '>';

                    return $builder->where($field, $operator, $search);
                });

            case 'hold':
                return $builder->whereHas('profile', function (Builder $builder) use ($search) {
                    $field = PublisherProfile::getHoldFieldByCurrencyId((int)request('currency_id'));
                    $operator = request('constraint') === 'less' ? '<' : '>';

                    return $builder->where($field, $operator, $search);
                });
        }

        return $builder;
    }

    public function flows()
    {
        return $this->hasMany(Flow::class);
    }
}
