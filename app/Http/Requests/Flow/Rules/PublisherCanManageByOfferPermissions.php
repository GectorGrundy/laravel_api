<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow\Rules;

use App\Models\Offer;
use Illuminate\Contracts\Validation\Rule;

/**
 * Валидация возможности доб/ред потока на основе прав доступа и активности оффера
 */
class PublisherCanManageByOfferPermissions implements Rule
{
    public function passes($attribute, $value): bool
    {
        $offer = Offer::isActiveForPublisher(\Auth::user())->where('hash', $value)->first();
        if (\is_null($offer)) {
            return false;
        }

        return !$offer->isInactiveForPublisher();
    }

    public function message(): string
    {
        return trans('api.forbidden');
    }
}