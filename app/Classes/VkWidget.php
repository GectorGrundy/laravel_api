<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;

/**
 * Класс для работы с Vk Retargeting
 */
class VkWidget
{
    public function getSctipt(?Flow $flow): string
    {
        if (\is_null($flow) || \is_null($flow->vk_widgets)) {
            return '';
        }

        $script_content = '';

        foreach ($flow->vk_widgets as $vk_widget) {
            $script_content .= str_replace(
                    '{COUNTER_ID}',
                    $vk_widget['attributes_array']['id'],
                    $this->getVkWidgetScriptFile()) . "\n\r";
        }

        return $script_content;
    }

    private function getVkWidgetScriptFile()
    {
        return \File::get(storage_path('files/vk_widget.html'));
    }
}
