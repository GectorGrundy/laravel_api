<?php
declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\{
    Model, Builder, Scope
};

class AlreadyAddedOfferScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = \Auth::user();

        if (request('with_already_added') && !\is_null($user) && $user->isPublisher()) {

            $builder->addSelect(
                \DB::raw("
                    IFNULL((SELECT 1 FROM `my_offers` 
                    WHERE `my_offers`.`offer_id` = `offers`.`id`
                    AND `my_offers`.`publisher_id` = {$user['id']}
                    LIMIT 1
                    ), 0) as already_added")
            );
        }
    }
}
