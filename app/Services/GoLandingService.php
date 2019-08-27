<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\TargetGeo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class GoLandingService
{
    public function getListForLanding(int $target_id, int $publisher_id = 0, int $locale_id = 0, int $visitor_country_id): Collection
    {
        $target_geo_list = TargetGeo::with([
            'country', 'target', 'price_currency', 'payout_currency',
            'country' => function ($query) use ($locale_id) {
                $query->translate($locale_id);
            }
        ])
            ->where('target_id', $target_id)
            ->active()
            ->get();

        if ($target_geo_list === null) {
            throw new ModelNotFoundException('Failed to get target geo info');
        }

        if ($publisher_id > 0) {
            (new TargetGeo())->replaceCustomStakes($target_geo_list, $publisher_id);
        }

        // Формируем массив с нужными данными для лендинга
        $target_geo_for_landing = collect();
        foreach ($target_geo_list AS $target_geo_item) {

            $target_geo_for_landing->push([
                'country_title' => $target_geo_item['country']['title'],
                'country_id' => $target_geo_item['country']['id'],
                'country_code' => $target_geo_item['country']['code'],
                'target_geo_hash' => $target_geo_item['hash'],
                'price' => $target_geo_item->price_currency->getFormattedPrice((int)$target_geo_item['price']),
                'old_price' => $target_geo_item->price_currency->getFormattedPrice((int)$target_geo_item['old_price']),
                'is_default' => $target_geo_item['is_default'],
            ]);
        }

        $detect_country = $target_geo_for_landing->where('country_id', $visitor_country_id)->all();

        if (\count($detect_country) < 1) {
            $default_target_geo = $target_geo_for_landing->first();

            $target_geo_for_landing->prepend([
                'country_title' => trans('go.choose_country'),
                'country_id' => 0,
                'country_code' => $default_target_geo['country_title'],
                'target_geo_hash' => '00000000',
                'price' => $default_target_geo['price'],
                'old_price' => $default_target_geo['old_price'],
            ]);
        }

        return $target_geo_for_landing;
    }
}
