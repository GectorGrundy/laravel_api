<?php
declare(strict_types=1);

use App\Models\FlowWidget;
use Illuminate\Database\Seeder;

class FlowWidgetSeeder extends Seeder
{
    public function run()
    {
        FlowWidget::truncate();

        $flow_widgets = [[
            'title' => 'Facebook Pixel',
            'schema' => json_encode([
                'id' => 'required|numeric',
                'TransitPageView' => 'required|in:0,1',
                'LandingPageView' => 'required|in:0,1',
                'LandingViewContent' => 'required|in:0,1',
                'LandingLead' => 'required|in:0,1',
                'SuccessPageView' => 'required|in:0,1',
                'SuccessPurchase' => 'required|in:0,1',
                'init_by_image' => 'required|in:0,1',
                'send_purchase_payout' => 'required|in:0,1',
            ])
        ], [
            'title' => 'Yandex Metrica',
            'schema' => json_encode([
                'id' => 'required|numeric',
                'webvisor' => 'required|in:0,1'
            ])
        ], [
            'title' => 'Custom code',
            'schema' => json_encode([
                'TransitCode' => 'present|string',
                'LandingCode' => 'present|string',
                'SuccessCode' => 'present|string',
                'CorrectCode' => 'present|string',
            ])
        ], [
            'title' => 'VK Retargeting',
            'schema' => json_encode([
                'id' => 'required|numeric',
            ])
        ], [
            'title' => 'Rating@Mail.ru',
            'schema' => json_encode([
                'id' => 'required|numeric',
            ])
        ], [
            'title' => 'Google.Analitycs',
            'schema' => json_encode([
                'id' => 'required|numeric',
            ])
        ]];

        foreach ($flow_widgets as $flow_widget) {
            FlowWidget::create($flow_widget);
        }
    }
}
