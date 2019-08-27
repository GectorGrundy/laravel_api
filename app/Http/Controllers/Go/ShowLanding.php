<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Classes\{
    CustomCodeWidget, DeviceInspector, FacebookPixel, GoogleAnalitycs, LandingHandler, RatingMailRu, VkWidget, YandexMetrika
};
use App\Http\Controllers\Controller;
use App\Http\GoDataContainer;
use App\Http\Requests\Go\GoRequest;
use App\Models\{
    Click, Domain, Landing, Offer
};
use App\Services\GoLandingService;
use App\Services\LandingUrlResolver;
use App\Support\LandingFileCompiler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowLanding extends Controller
{
    private $landing_handler;
    private $go_request;
    /**
     * @var GoDataContainer
     */
    private $data_container;
    /**
     * @var GoLandingService
     */
    private $landing_service;
    /**
     * @var LandingFileCompiler
     */
    private $landing_file_compiler;
    /**
     * @var YandexMetrika
     */
    private $yandex_metrika;
    /**
     * @var FacebookPixel
     */
    private $facebook_pixel;
    /**
     * @var DeviceInspector
     */
    private $device_inspector;
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
        LandingHandler $landing_handler, GoRequest $go_request, GoDataContainer $data_container,
        GoLandingService $landing_service, LandingFileCompiler $landing_file_compiler,
        YandexMetrika $yandex_metrika, FacebookPixel $facebook_pixel, DeviceInspector $device_inspector,
        GoogleAnalitycs $google_analitycs, RatingMailRu $rating_mail_ru, VkWidget $vk_widget,
        CustomCodeWidget $custom_code_widget
    )
    {
        $this->landing_handler = $landing_handler;
        $this->go_request = $go_request;
        $this->data_container = $data_container;
        $this->landing_service = $landing_service;
        $this->landing_file_compiler = $landing_file_compiler;
        $this->yandex_metrika = $yandex_metrika;
        $this->facebook_pixel = $facebook_pixel;
        $this->device_inspector = $device_inspector;
        $this->google_analitycs = $google_analitycs;
        $this->rating_mail_ru = $rating_mail_ru;
        $this->vk_widget = $vk_widget;
        $this->custom_code_widget = $custom_code_widget;
    }

    public function __invoke()
    {
        $landing = $this->data_container->getLanding();

        if ($landing['is_external']) {
            return $this->goToExternalRedirectPage();
        }

        return $this->showLanding();
    }

    private function showLanding()
    {
        $url_params = $this->go_request->getRequiredUrlParams();

        $landing = $this->data_container->getLanding();
        $flow = $this->data_container->getFlow();
        $visitor = $this->data_container->getVisitor();

        $target_geo_list = $this->landing_service->getListForLanding(
            $landing['target_id'],
            $flow['publisher_id'],
            $landing['locale_id'],
            (int)$visitor['geo_ids']['country_id']
        );

        $html = $this->landing_file_compiler->compile(
            $landing->domain,
            LandingUrlResolver::INDEX_PAGE,
            ['target_geo_list' => $target_geo_list]
        );

        // Получение содержимого файла скриптов для лендинга
        $landing_script = $this->getLandingScript($target_geo_list);

        // Получаем реквизиты оффера, если это разрешено в настройках потока
        if ($flow['is_show_requisite']) {
            try {
                $offer_requisites = (new Offer())->getRequisiteByLocale(
                    $this->data_container->getOffer()['id'],
                    $this->data_container->getLocale()['id']
                );
            } catch (ModelNotFoundException $e) {
                // Если реквизиты по текущей локали не найдены, оставляем их пустыми
            }
        }

        $yandex_metrika_script = $this->yandex_metrika->getScript($flow, 'landing');
        $facebook_pixel_script = $this->facebook_pixel->getScript($flow, 'landing');
        $flow_custom_code = $this->custom_code_widget->getScript($flow, 'landing');
        $google_analitycs_script = $this->google_analitycs->getScript($flow);
        $rating_mail_ru_script = $this->rating_mail_ru->getScript($flow);
        $vk_widget_script = $this->vk_widget->getSctipt($flow);


        $html = $this->replaceLandingTokens($html, [
            'scripts_file' => $landing_script,
            'yandex_metrika_script' => $yandex_metrika_script,
            'offer_requisites' => $offer_requisites ?? '',
            'facebook_pixel_script' => $facebook_pixel_script,
            'custom_html_code' => $flow_custom_code,
            'url_params' => $url_params,
            'target_geo_list' => $target_geo_list,
            'google_analitycs_script' => $google_analitycs_script,
            'rating_mail_ru_script' => $rating_mail_ru_script,
            'vk_widget_script' => $vk_widget_script,
        ]);

        return response($html);
    }

    /**
     * Замена токенов лендинга и вставка нужных переменных на страницу
     *
     * @param string $html
     * @param array $params
     * @return mixed
     */
    private function replaceLandingTokens(string $html, array $params)
    {
        $order_url = LandingUrlResolver::getOrderPageUrl($params['url_params']);

        return str_replace([
            '<head>',
            '<head>',
            '{PRIVACY_POLICY_URL}',
            '{TERMS_URL}',
            '{RETURNS_URL}',
            '{REQUISITES}',
            '</body>',
            '</body>',
            '</body>',
            '</body>',
            '</body>',
            '</body>',
            '</body>',
            '</body>',
        ], [
            "<head><script>var ORDER_PAGE_URL = '{$order_url}'</script>",
            '<head>' . $this->landing_handler->getBaseTag(),
            LandingUrlResolver::getPrivacyPolicyPageUrl(),
            LandingUrlResolver::getTermsUrl(),
            LandingUrlResolver::getReturnsUrl(),
            "{$params['offer_requisites']}",
            "<script>{$params['scripts_file']}</script></body>",
            "{$params['yandex_metrika_script']}</body>",
            "{$params['facebook_pixel_script']}</body>",
            "{$params['custom_html_code']}</body>",
            "{$params['rating_mail_ru_script']}</body>",
            "{$params['vk_widget_script']}</body>",
            "{$params['google_analitycs_script']}</body>",
            $this->landing_handler->getLandingModals(),
        ],
            $html
        );
    }

    private function getLandingScript($target_geo_list): string
    {
        $flow = $this->data_container->getFlow();
        $landing = $this->data_container->getLanding();

        return str_replace([
            '{INCORRECT_PHONE_NUMBER_MSG}',
            '{INCORRECT_TARGET_GEO_MSG}',

            '"{TARGET_GEO_LIST}"',
            '"{VISITOR_COUNTRY_ID}"',
            '"{NOT_SELECTED_TARGET_GEO}"',

            '"{FLOW_IS_HIDE_TARGET_GEO_LIST}"',
            '"{FLOW_HASH}"',
            '"{FLOW_BACK_ACTION_SEC}"',
            '"{FLOW_BACK_CALL_BTN_SEC}"',
            '"{FLOW_BACK_CALL_FORM_SEC}"',
            '"{FLOW_VIBRATE_ON_MOBILE_SEC}"',

            '"{LANDING_IS_BACK_ACTION}"',
            '"{LANDING_IS_BACK_CALL}"',
            '"{LANDING_IS_VIBRATE_ON_MOBILE}"',
        ], [
            trans('go.incorrect_phone_number_msg'),
            trans('go.incorrect_target_geo_msg'),
            json_encode($target_geo_list),
            (int)$this->data_container->getVisitor()['geo_ids']['country_id'],
            '"00000000"',

            $flow['is_hide_target_list'],
            $flow['hash'],

            $flow['back_action_sec'] === '' ? var_export(false, true) : $flow['back_action_sec'],
            $flow['back_call_btn_sec'] === '' ? var_export(false, true) : $flow['back_call_btn_sec'],
            $flow['back_call_form_sec'] === '' ? var_export(false, true) : $flow['back_call_form_sec'],
            $flow['vibrate_on_mobile_sec'] === '' ?
                var_export(false, true)
                : $flow['vibrate_on_mobile_sec'],

            $landing['is_back_action'],
            $landing['is_back_call'],
            $landing['is_vibrate_on_mobile'],
        ],
            \File::get(public_path('js/landing.js'))
        );
    }

    private function goToExternalRedirectPage()
    {
        $click = $this->createClick();

        return redirect()->away($this->getExternalLandingRedirectUrl($click));
    }

    private function getExternalLandingRedirectUrl(Click $click)
    {
        $redirect_domain = Domain::getDefaultRedirect();
        $landing_url = $this->getLandingUrl($click);

        if ($click->target_geo->isRedirect()) {
            return $redirect_domain['host'] . '/rdr/' . $click['hash'] . '?to=' . urlencode($landing_url);
        }

        return $landing_url;
    }

    private function getLandingUrl(Click $click): string
    {
        return str_replace([
            '{clickid}',
            '{publisher_hash}',
            '{offer_hash}',
        ], [
            $click['hash'],
            $this->data_container->getFlow()->user['hash'],
            $this->data_container->getOffer()['hash'],
        ],
            $this->data_container->getLanding()->system_domain['host']
        );
    }

    private function createClick(): Click
    {
        $visitor = $this->data_container->getVisitor();
        $landing = $this->data_container->getLanding();
        $flow = $this->data_container->getFlow();
        $target_geo = $this->data_container->getVisitorTargetGeo();
        $device_info = $this->device_inspector->getDeviceIdentifiers($visitor['user_agent']);

        return Click::create([
            'domain_id' => $this->data_container->getCurrentDomain()['id'],
            'advertiser_id' => $target_geo->integration['advertiser_id'],
            'advertiser_payout' => $target_geo->integration['charge'],
            'advertiser_currency_id' => $target_geo->integration['currency_id'],
            'target_geo_id' => $target_geo['id'],
            'country_id' => $target_geo['country_id'],
            'region_id' => $visitor['geo_ids']['region_id'],
            'city_id' => $visitor['geo_ids']['city_id'],
            'landing_id' => $landing['id'],
            'transit_id' => $this->data_container->getFromTransitId(),
            'flow_id' => $flow['is_virtual'] ? 0 : $flow['id'],
            's_id' => $visitor['s_id'],
            'ip' => $visitor['ip'],
            'ip_country_id' => $visitor['geo_ids']['country_id'],
            'user_agent' => $visitor['user_agent'],
            'data1' => $this->data_container->getData1(),
            'data2' => $this->data_container->getData2(),
            'data3' => $this->data_container->getData3(),
            'data4' => $this->data_container->getData4(),
            'clickid' => $this->data_container->getClickid(),
            'referer' => $visitor['referer'],
            'browser_id' => $device_info['browser_id'],
            'os_platform_id' => $device_info['os_platform_id'],
            'device_type_id' => $device_info['device_type_id'],
            'is_extra_flow' => $this->data_container->isExtraFlow(),
            'transit_traffic_type' => $this->data_container->getFromTransitTrafficType(),
            'browser_locale' => $visitor['browser_locale'],
            'ips' => json_encode($visitor['ips']),
            'initialized_at' => $this->data_container->getFlowClickDate()->toDateTimeString()
        ]);
    }
}
