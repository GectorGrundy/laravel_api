<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;
use App\Models\FlowFlowWidget;
use Exception;

/**
 * Класс для работы с Facebook Pixel
 */
class FacebookPixel
{
    /**
     * Получение скрипта Facebook Pixel
     *
     * @param Flow $flow
     * @param string $page
     *
     * @return string
     */
    public function getScript(?Flow $flow, string $page): string
    {
        if (\is_null($flow) || \is_null($flow->facebook_pixel_widgets)) {
            return '';
        }

        $pixels = $this->getPixels($flow, $page);

        if (!\count($pixels)) {
            return '';
        }

        return $this->insertPixelsIntoFile($pixels);
    }

    private function getPixels(Flow $flow, string $page): array
    {
        $pixels = [];
        foreach ($flow->facebook_pixel_widgets as $widget) {

            $pixel = $this->getPixelConfig($widget, $page);

            if (!\is_null($pixel)) {
                $pixels[] = $pixel;
            }
        }

        return $pixels;
    }

    private function getPixelConfig(FlowFlowWidget $widget, string $page): ?array
    {
        $page = ucfirst($page);

        $events = [
            $widget['attributes_array'][$page . 'PageView'] ?? 0,
            $widget['attributes_array'][$page . 'ViewContent'] ?? 0,
            $widget['attributes_array'][$page . 'AddToCart'] ?? 0,
            $widget['attributes_array'][$page . 'InitiateCheckout'] ?? 0,
            $widget['attributes_array'][$page . 'Lead'] ?? 0,
            $widget['attributes_array'][$page . 'FinalPage'] ?? 0,
            $widget['attributes_array'][$page . 'Purchase'] ?? 0,
        ];

        $active_event_exists = \in_array(1, $events, true);
        if (!$active_event_exists) {
            return null;
        }
        $id = $widget['attributes_array']['id'];

        if (request()->filled('fb_pixel_id')) {
            $id = request('fb_pixel_id');
        }

        return [
            'id' => $id,
            'isPageView' => $widget['attributes_array'][$page . 'PageView'] ?? 0,
            'isViewContent' => $widget['attributes_array'][$page . 'ViewContent'] ?? 0,
            'isAddToCart' => $widget['attributes_array'][$page . 'AddToCart'] ?? 0,
            'isInitiateCheckout' => $widget['attributes_array'][$page . 'InitiateCheckout'] ?? 0,
            'isLead' => $widget['attributes_array'][$page . 'Lead'] ?? 0,
            'isFinalPage' => $widget['attributes_array'][$page . 'FinalPage'] ?? 0,
            'send_purchase_payout' => $widget['attributes_array']['send_purchase_payout'],
            'init_by_image' => $widget['attributes_array']['init_by_image'],
        ];
    }

    private function insertPixelsIntoFile(array $pixels)
    {
        return str_replace("'{PIXELS}'", json_encode($pixels), $this->getFacebookPixelScriptFile());
    }

    /**
     * Получение скрипта facebook pixel
     *
     * @return mixed
     * @throws Exception
     */
    private function getFacebookPixelScriptFile()
    {
        return \File::get(storage_path('files/facebook_pixel.html'));
    }
}
