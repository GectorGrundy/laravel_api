<?php
declare(strict_types=1);

use App\Models\FlowFlowWidget;
use App\Models\FlowWidget;
use Illuminate\Database\Seeder;

class FlowFlowWidgetSeeder extends Seeder
{
    public function run()
    {
        FlowFlowWidget::create([
            'flow_id' => 2, // Chocolite flow of test publisher
            'flow_widget_id' => FlowWidget::FACEBOOK_PIXEL,
            'attributes' => json_encode([
                'id' => 3333,
                'TransitPageView' => 1,
                'LandingPageView' => 1,
                'LandingViewContent' => 1,
                'LandingLead' => 1,
                'init_by_image' => 0,
                'send_purchase_payout' => 0,
            ]),
        ]);

        FlowFlowWidget::create([
            'flow_id' => 2, // Chocolite flow of test publisher
            'flow_widget_id' => FlowWidget::YANDEX_METRIKA,
            'attributes' => json_encode([
                'id' => 3333,
                'webvisor' => 1
            ]),
        ]);

        FlowFlowWidget::create([
            'flow_id' => 2, // Chocolite flow of test publisher
            'flow_widget_id' => FlowWidget::CUSTOM_CODE,
            'attributes' => json_encode([
                'TransitCode' => "<script>console.log('transit')</script>",
                'LandingCode' => "<script>console.log('landing')</script>",
                'SuccessCode' => "<script>console.log('success')</script>",
                'CorrectCode' => "<script>console.log('correct')</script>",
            ]),
        ]);
    }
}
