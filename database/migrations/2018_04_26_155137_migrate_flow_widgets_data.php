<?php
declare(strict_types=1);

use App\Models\FlowFlowWidget;
use Illuminate\Database\Migrations\Migration;

class MigrateFlowWidgetsData extends Migration
{
    public function up()
    {
        $only_attributes = [
            'id', 'TransitPageView', 'LandingPageView', 'LandingViewContent', 'LandingLead', 'SuccessPageView',
            'SuccessPurchase', 'init_by_image', 'send_purchase_payout',
        ];

        FlowFlowWidget::all()->each(function (FlowFlowWidget $widget) use ($only_attributes) {
            $attributes = collect($widget['attributes_array'])->only($only_attributes)->toArray();

            $widget->update([
                'attributes' => json_encode($attributes),
            ]);
        });
    }
}
