<?php
declare(strict_types=1);

namespace App\Classes;

use App\Http\GoDataContainer;
use App\Services\LandingUrlResolver;
use Cache;
use App\Models\{
    Visitor, Flow, Transit, Domain
};
use App\Support\LandingFileCompiler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request AS IlluminateRequest;

class TransitHandler
{
    use DispatchesJobs;

    private $flow;
    private $request;
    private $visitor;
    private $transit;
    private $domain;
    private $yandex_metrika;
    private $facebook_pixel;
    private $landing_file_compiler;
    private $data_container;
    /**
     * @var GoogleAnalitycs
     */
    private $google_analitycs;
    /**
     * @var RatingMailRu
     */
    private $rating_mail_ru;
    /**
     * @var VkWidget
     */
    private $vk_widget;
    /**
     * @var CustomCodeWidget
     */
    private $custom_code_widget;

    public function __construct(
        IlluminateRequest $request, Flow $flow, Visitor $visitor, Transit $transit, Domain $domain,
        YandexMetrika $yandex_metrika, FacebookPixel $facebook_pixel,
        LandingFileCompiler $landing_file_compiler, GoDataContainer $data_container,
        GoogleAnalitycs $google_analitycs, RatingMailRu $rating_mail_ru, VkWidget $vk_widget,
        CustomCodeWidget $custom_code_widget

    )
    {
        $this->request = $request;
        $this->flow = $flow;
        $this->visitor = $visitor;
        $this->transit = $transit;
        $this->domain = $domain;
        $this->yandex_metrika = $yandex_metrika;
        $this->facebook_pixel = $facebook_pixel;
        $this->landing_file_compiler = $landing_file_compiler;
        $this->data_container = $data_container;
        $this->google_analitycs = $google_analitycs;
        $this->rating_mail_ru = $rating_mail_ru;
        $this->vk_widget = $vk_widget;
        $this->custom_code_widget = $custom_code_widget;
    }

    /**
     * Замена токенов прелендинга
     *
     * @param $html
     * @param $params
     * @return mixed
     */
    private function replaceTransitTokens($html, $params)
    {
        $html = str_replace(
            [
                '<head>',
                '{LANDING_URL}',
                '</body>',
                '</body>',
                '</body>',
                '</body>',
                '</body>',
                '</body>',
                '</body>',
                '</body>',
            ],
            [
                '<head>' . app(LandingHandler::class)->getBaseTag(),
                "{$params['landing_url']}",
                "<script>{$params['scripts_file']}</script></body>",
                "{$params['yandex_metrika_script']}</body>",
                "<script>{$params['combacker_content']}</script></body>",
                "{$params['facebook_pixel_script']}</body>",
                "{$params['custom_html_code']}</body>",
                "{$params['rating_mail_ru_script']}</body>",
                "{$params['vk_widget_script']}</body>",
                "{$params['google_analitycs_script']}</body>",
            ],
            $html);

        $html = preg_replace(
            [
                '/<body( .*?)>/imsx',
                '/<body( .*?)>/imsx',
            ],
            [
                "<body$1><script>var locale = '{$params['transit_locale_code']}';" . $params['dtime_file'] . '</script>',
                '<body$1><script src=//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js></script>',
            ],
            $html);

        return $html;
    }

    /**
     * Получение содержимого файла dtime.js
     *
     * @return string
     */
    private function getDtimeFile(): string
    {
        return \File::get(public_path('js/dtime.js'));
    }

    /**
     * Получение файла скриптов для лендинга
     *
     * @param array $params
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getTransitScript(array $params): string
    {
        // Получение содержимого файла скриптов
        $landing_script = $this->getTransitScriptFile();

        // Замена токенов в файле скриптов
        $landing_script = $this->replaceTransitScriptTokens($landing_script, $params);

        return $landing_script;
    }

    /**
     * Получаем прелендинг для перенаправление на нее посетителя
     *
     * @return Transit
     */
    public function getTransitForShow(): Transit
    {
        $flow = $this->data_container->getFlow();
        $visitor = $this->data_container->getVisitor();
        $flow_transits = $flow->transits;

        if (!$flow_transits->count()) {
            throw new ModelNotFoundException();
        }

        // Если в сплите потока указано запоминать историю посещений
        $visitor_transit_id = 0;
        if ($flow['is_remember_transit'] === 1) {

            // Получаем id прелендинга оффера, если он переходил раньше на нее
            $visitor_transit_id = $this->visitor->getViewedTransitIdForFlow($visitor['info'], $flow);
        }

        // Получаем идентификаторы прелендингов потока
        $flow_transit_ids = $flow_transits->pluck('id')->toArray();

        // Если в истории у посетителя есть показанный прелендинг и она есть в текущих настройках потока - берем ее
        if ($visitor_transit_id > 0 && in_array($visitor_transit_id, $flow_transit_ids)) {
            $transit = $this->transit->getInfo($visitor_transit_id);
        } else {
            // Получаем прелендинг для перенаправления из списка прелендингов потока
            $transit = $this->getTransitForShowByRandom($flow['id'], $flow_transits);
        }

        return $transit;
    }

    /**
     * Проверка выбранных прелендингов потока есть ли такие, которые подходят для нужной локали
     *
     * @param array $flow_transits
     * @param int $locale_id
     * @return bool
     */
    private function checkFlowTransitsForNeedleLocaleId(array $flow_transits, int $locale_id): bool
    {
        $exists_for_needle_locale = FALSE;

        foreach ($flow_transits AS &$flow_transit) {
            if ($locale_id && $flow_transit['transit_locale_id'] === $locale_id) {
                $exists_for_needle_locale = TRUE;
            }
        }

        return $exists_for_needle_locale;
    }

    /**
     * Получаем прелендинги для посетителя
     *
     * @param $flow_id
     * @param $flow_transits
     * @return Transit
     */
    private function getTransitForShowByRandom($flow_id, $flow_transits): Transit
    {
        // Получаем статистику переходов на прелендинги потока
        $showed_transits = $this->getShowedTransitsInFlow($flow_id);

        $exists_not_showed = FALSE;

        foreach ($flow_transits AS $flow_transit) {

            //Если в доступных для перехода прелендингах есть те, на которые еще не переходили - отдаем первый попавшийся
            if (!array_key_exists($flow_transit['id'], $showed_transits)) {
                $exists_not_showed = TRUE;

                $transit_id = $flow_transit['id'];

                $showed_transits[$transit_id]['count_show'] = 1;
                $showed_transits[$transit_id]['transit_id'] = $transit_id;
                break;
            }
        }

        // Если нету прелендингов, на которые еще не переходили посетители
        if (!$exists_not_showed) {

            $showed_transits_by_random_collected = collect($showed_transits);

            //Сортировка по кол-ву показов(ASC)
            $showed_transits_by_random_collected = $showed_transits_by_random_collected->sortBy('count_show')->toArray();

            //Выбираем прелендинг, у которой было наименьшее кол-во показов
            $transit_id = current($showed_transits_by_random_collected)['transit_id'];

            //Инкремент кол-во показов
            $showed_transits[$transit_id]['count_show'] = $showed_transits[$transit_id]['count_show'] + 1;
        }

        //Записуем обновленную статистику по показанным прелендингам потока
        $this->setShowedTransitsInFlow($flow_id, $showed_transits);

        return (new Transit())->getInfo($transit_id);
    }

    /**
     * Получение статистики переходов на прелендинги потока
     *
     * @param $flow_id
     * @return array
     */
    private function getShowedTransitsInFlow($flow_id)
    {
        $key = "go:getShowedTransitsInFlow:{$flow_id}";

        $showed_offers = Cache::tags("flow-{$flow_id}")->get($key);

        return $showed_offers === null ? [] : json_decode($showed_offers, TRUE);
    }

    /**
     * Записуем в кеш статистику переходов на прелендинги потока
     *
     * @param $flow_id
     * @param $showed_transits
     */
    public function setShowedTransitsInFlow($flow_id, $showed_transits)
    {
        $key = "go:getShowedTransitsInFlow:{$flow_id}";

        Cache::tags("flow-{$flow_id}")->forever($key, json_encode($showed_transits));
    }

    /**
     * Получение HTML прелендинга
     *
     * @param string $landing_url
     * @param bool $landing_target_blank
     * @return string
     */
    public function getTransitHtml(string $landing_url, bool $landing_target_blank): string
    {
        $html = $this->landing_file_compiler->compile(
            $this->data_container->getTransit()->domain,
            LandingUrlResolver::INDEX_PAGE
        );
        $flow = $this->data_container->getFlow();
        $transit = $this->data_container->getTransit();

        // Если в настройках потока есть дополнительный поток - формируем ссылку на него
        if (!\is_null($flow->extra_flow)) {
            $extra_flow_url = 'http://' . $this->domain->getActiveTdsDomain() . '/click/' . $flow['extra_flow']['hash']
                . '?is_extra_flow=1';
        }

        // Получаем файл скриптов для прелендинга и заменяем в нем токены
        $transit_js = $this->getTransitScript([
            'landing_url' => $landing_url,
            'extra_flow_url' => $extra_flow_url ?? '',
            'landing_target_blank' => $landing_target_blank,
        ]);

        $yandex_metrika_script = $this->yandex_metrika->getScript($flow, 'transit');
        $facebook_pixel_script = $this->facebook_pixel->getScript($flow, 'transit');
        $flow_custom_code = $this->custom_code_widget->getScript($flow, 'transit');
        $google_analitycs_script = $this->google_analitycs->getScript($flow);
        $rating_mail_ru_script = $this->rating_mail_ru->getScript($flow);
        $vk_widget_script = $this->vk_widget->getSctipt($flow);


        // Заменяем токены
        return $this->replaceTransitTokens($html, [
            'scripts_file' => $transit_js,
            'yandex_metrika_script' => $yandex_metrika_script,
            'dtime_file' => $this->getDtimeFile(),
            'combacker_content' => '',
            'facebook_pixel_script' => $facebook_pixel_script,
            'custom_html_code' => $flow_custom_code,
            'transit_locale_code' => $transit->locale['code'],
            'landing_url' => $landing_url,
            'google_analitycs_script' => $google_analitycs_script,
            'rating_mail_ru_script' => $rating_mail_ru_script,
            'vk_widget_script' => $vk_widget_script,
        ]);
    }

    /**
     * Замена токенов в скрипте прелендинга
     *
     * @param $script_content
     * @param $params
     * @return mixed
     */
    private function replaceTransitScriptTokens($script_content, $params)
    {
        $flow = $this->data_container->getFlow();

        $script_content = str_replace(
            [
                '"{LANDING_URL}"',
                '"{LANDING_TARGET_BLANK}"',
                '"{EXTRA_FLOW_URL}"',
                '"{IS_MOBILE}"',
                '"{FLOW_IS_NOBACK}"',
                '"{FLOW_IS_COMEBACKER}"',
            ],
            [
                "'{$params['landing_url']}'",
                var_export((bool)$params['landing_target_blank'], true),
                "'{$params['extra_flow_url']}'",
                var_export((bool)$this->data_container->getVisitor()['is_mobile'], true),
                var_export((bool)$flow['is_noback'], true),
                var_export((bool)$flow['is_comebacker'], true),
            ],
            $script_content
        );

        return $script_content;
    }

    /**
     * Получение содержимого файла скриптов прелендинга
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getTransitScriptFile(): string
    {
        return \File::get(public_path('/js/transit.js'));
    }
}