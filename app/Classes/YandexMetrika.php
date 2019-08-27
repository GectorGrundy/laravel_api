<?php
declare(strict_types=1);

namespace App\Classes;

use App\Models\Flow;

/**
 * Класс для работы с Yandex Metrika
 */
class YandexMetrika
{
    public const WEBVISOR_PAGES = ['transit', 'landing'];

    public function getScript(?Flow $flow, string $page): string
    {
        if (\is_null($flow) || \is_null($flow->yandex_metrika_widgets)) {
            return '';
        }

        $script_content = '';

        foreach ($flow->yandex_metrika_widgets as $widget) {
            $id = $widget['attributes_array']['id'];

            $webvisor = $widget['attributes_array']['webvisor'];

            $script_content .= $this->replaceYandexMetrikaScriptTokens(
                    $id,
                    $this->webvisorIsAllowed($webvisor, $page)
                ) . "\r\n";
        }

        return $script_content;
    }

    /**
     * Получение скрипта yandex metrika
     *
     * @return string
     */
    private function getYandexMetrikaScriptFile(): string
    {
        return \File::get(storage_path('files/yandex_metrika_script.html'));
    }

    /**
     * Замена токенов в скрипте yandex metrika
     *
     * @param $counter_id
     * @param $is_yandex_webvisor
     * @return mixed
     */
    private function replaceYandexMetrikaScriptTokens($counter_id, $is_yandex_webvisor)
    {
        $script_content = str_replace([
            "'{COUNTER_ID}'",
            "'{IS_WEBVISOR}'"
        ], [
            $counter_id,
            var_export($is_yandex_webvisor, true)
        ],
            $this->getYandexMetrikaScriptFile()
        );

        return $script_content;
    }

    private function webvisorIsAllowed($webvisor, string $page): bool
    {
        return in_array($page, self::WEBVISOR_PAGES) && $webvisor;
    }
}