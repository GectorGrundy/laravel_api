<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;

/**
 * Класс для работы с Rating Mail Ru
 */
class RatingMailRu
{
    public function getScript(?Flow $flow): string
    {
        if (\is_null($flow) || \is_null($flow->rating_mail_ru_widgets)) {
            return '';
        }

        $script_content = '';

        foreach ($flow->rating_mail_ru_widgets as $widget) {
            $script_content .= str_replace(
                    '{COUNTER_ID}',
                    $widget['attributes_array']['id'],
                    $this->getRaitingMailRuScriptFile()) . "\n\r";
        }

        return $script_content;
    }

    private function getRaitingMailRuScriptFile(): string
    {
        return \File::get(storage_path('files/rating_mail_ru.html'));
    }
}
