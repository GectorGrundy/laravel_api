<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;

/**
 * Класс для работы с виджетом кастомного кода потока
 */
class CustomCodeWidget
{
    public function getScript(?Flow $flow, string $page): string
    {
        if (\is_null($flow) || \is_null($flow->custom_html_widgets)) {
            return '';
        }

        $script_content = '';

        foreach ($flow->custom_html_widgets as $widget) {

            $code = $widget['attributes_array'][ucfirst($page) . 'Code'];

            if (!empty($code) && $widget['is_moderated']) {
                $script_content .= "<!-- Custom Code -->{$code}<!-- /Custom Code -->";
            }
        }

        return $script_content;
    }
}
