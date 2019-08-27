<?php
declare(strict_types=1);

namespace App\Observers\Order;

use App\Models\Order;
use Illuminate\Foundation\Bus\DispatchesJobs;

class FillFieldsFromInfoField
{
    use DispatchesJobs;

    public function creating(Order $order)
    {
        if (is_null($order->info)) {
            $order->info = '{}';
        } else {
            $info_array = json_decode($order->info, true);

            $order->country_id = $info_array['country_id'] ?? 0;
            $order->product_cost = $info_array['product_cost'] ?? 0;
            $order->product_cost_sign = $info_array['product_cost_sign'] ?? '';
            $order->delivery_cost = $info_array['delivery_cost'] ?? 0;
            $order->delivery_cost_sign = $info_array['delivery_cost_sign'] ?? '';
            $order->total_cost = $info_array['total_cost'] ?? 0;
            $order->total_cost_sign = $info_array['total_cost_sign'] ?? '';
            $order->email = $order->email ?? $info_array['email'] ?? '';
            $order->name = $order->name ?? $info_array['name'] ?? '';
            $order->last_name = $info_array['last_name'] ?? '';
            $order->street = $info_array['street'] ?? '';
            $order->house = $info_array['house'] ?? '';
            $order->apartment = $info_array['apartment'] ?? '';
            $order->zipcode = $info_array['zipcode'] ?? '';
            $order->city = $info_array['city'] ?? '';
            $order->tax_cost = $info_array['tax_cost'] ?? '';
            $order->tax_cost_sign = $info_array['tax_cost_sign'] ?? '';
        }
    }

    public function updating(Order $order)
    {
        $info_array = json_decode($order->info, true);

        if (!count($info_array)) {
            return;
        }

        $order->country_id = $info_array['country_id'] ?? 0;
        $order->email = $order->email ?? $info_array['email'] ?? '';
        $order->name = $order->name ?? $info_array['name'] ?? '';
        $order->street = $info_array['street'] ?? '';
        $order->house = $info_array['house'] ?? '';
        $order->apartment = $info_array['apartment'] ?? '';
        $order->zipcode = $info_array['zipcode'] ?? '';
        $order->city = $info_array['city'] ?? '';
    }
}
