<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;

/**
 * Класс для работы с Google Nanalitycs
 */
class GoogleAnalitycs
{
    public function getScript(?Flow $flow): string
    {
        if (\is_null($flow) || \is_null($flow->google_analitycs_widgets)) {
            return '';
        }

        $script_content = '';

        foreach ($flow->google_analitycs_widgets as $widget) {
            $script_content .= str_replace(
                    '{COUNTER_ID}',
                    $widget['attributes_array']['id'],
                    $this->getGoogleAnalitycsScriptFile()
                ) . "\n\r";
        }

        return $script_content;
    }

    private function getGoogleAnalitycsScriptFile(): string
    {
        return \File::get(storage_path('files/google_analitycs.html'));
    }
}
