<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Http\GoDataContainer;
use App\Integrations\Approveninja\ApproveninjaEditOrder;
use App\Services\GoLandingService;
use App\Strategies\LeadCreation\CodCorrectLeadCreation;
use App\Support\LandingFileCompiler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests\Go\GoRequest;
use App\Http\Controllers\Controller;
use App\Models\{
    Integration, Lead, TargetGeo, Order
};
use App\Classes\{
    CustomCodeWidget, FacebookPixel, GoogleAnalitycs, LandingHandler, PhoneInspector, RatingMailRu, VkWidget, YandexMetrika
};

class CodOrderController extends Controller
{
    private $go_request;
    private $landing_handler;
    private $facebook_pixel;
    private $yandex_metrika;
    private $request;
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
        Request $request, GoRequest $go_request, LandingHandler $landing_handler, FacebookPixel $facebook_pixel,
        YandexMetrika $yandex_metrika, GoDataContainer $data_container,
        GoogleAnalitycs $google_analitycs, RatingMailRu $rating_mail_ru, VkWidget $vk_widget,
        CustomCodeWidget $custom_code_widget
    )
    {
        $this->go_request = $go_request;
        $this->landing_handler = $landing_handler;
        $this->facebook_pixel = $facebook_pixel;
        $this->yandex_metrika = $yandex_metrika;
        $this->request = $request;
        $this->data_container = $data_container;
        $this->google_analitycs = $google_analitycs;
        $this->rating_mail_ru = $rating_mail_ru;
        $this->vk_widget = $vk_widget;
        $this->custom_code_widget = $custom_code_widget;
    }

    /**
     * Отображение страницы успешного заказа cod лида
     *
     * @param LandingFileCompiler $view_compiler
     * @param string $lead_hash
     * @param string $is_iframe
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSuccessPage(LandingFileCompiler $view_compiler, string $lead_hash, string $is_iframe)
    {
        $lead_info = (new Lead())->getByByHash($lead_hash, ['order', 'flow', 'landing', 'currency', 'locale']);

        $show_backlink = $this->landing_handler->getShowBackLinkOnSuccess($is_iframe);
        $view = $lead_info->landing['is_custom_success'] ? 'Custom::success' : 'success';

        $yandex_metrika_script = $this->yandex_metrika->getScript($lead_info['flow'], 'success');
        $facebook_pixel_script = $this->facebook_pixel->getScript($lead_info['flow'], 'success');
        $flow_custom_code = $this->custom_code_widget->getScript($lead_info['flow'], 'success');
        $google_analitycs_script = $this->google_analitycs->getScript($lead_info['flow']);
        $rating_mail_ru_script = $this->rating_mail_ru->getScript($lead_info['flow']);
        $vk_widget_script = $this->vk_widget->getSctipt($lead_info['flow']);

        $backlink = $this->data_container->getCurrentDomain()['host'] . '/?' . $_SERVER['QUERY_STRING'];

        return $view_compiler->compile($this->data_container->getLanding()->domain, $view, [
            'name' => $lead_info->order['name'],
            'phone' => $lead_info->order['phone'],
            'comment' => $lead_info->order['comment'],
            'lead_hash' => $lead_hash,
            'lead_payout' => $lead_info['payout'],
            'lead_currency_code' => $lead_info['currency']['code'],
            'flow_info' => $lead_info['flow'],
            'yandex_metrika_script' => $yandex_metrika_script,
            'facebook_pixel_script' => $facebook_pixel_script,
            'landing_info' => $lead_info['landing'],
            'show_backlink' => $show_backlink,
            'backlink' => $backlink,
            'custom_html_code' => $flow_custom_code,
            'base_tag' => $this->landing_handler->getBaseTag(),
            'google_analitycs_script' => $google_analitycs_script,
            'rating_mail_ru_script' => $rating_mail_ru_script,
            'vk_widget_script' => $vk_widget_script,
        ]);
    }

    /**
     * Отображение страницы редактирования заказа
     *
     * @param Lead $lead
     * @param GoLandingService $landing_service
     * @param string $lead_hash
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCorrectPage(Lead $lead, GoLandingService $landing_service, string $lead_hash)
    {
        $lead_info = $lead->getByByHash($lead_hash, ['order', 'flow', 'landing', 'target_geo', 'locale']);

        $yandex_metrika_script = $this->yandex_metrika->getScript($lead_info['flow'], 'correct');
        $facebook_pixel_script = $this->facebook_pixel->getScript($lead_info['flow'], 'correct');
        $flow_custom_code = $this->custom_code_widget->getScript($lead_info['flow'], 'correct');
        $google_analitycs_script = $this->google_analitycs->getScript($lead_info['flow']);
        $rating_mail_ru_script = $this->rating_mail_ru->getScript($lead_info['flow']);
        $vk_widget_script = $this->vk_widget->getSctipt($lead_info['flow']);

        $target_geo_list = $landing_service->getListForLanding(
            $lead_info['target_id'],
            $lead_info['publisher_id'],
            $lead_info['locale_id'],
            (int)$this->data_container->getVisitor()['geo_ids']['country_id']
        );

        return view('correct', [
            'name' => $lead_info['order']['name'],
            'phone' => $lead_info['order']['phone'],
            'target_geo_list' => $target_geo_list,
            'target_geo_hash' => $lead_info['target_geo']['hash'],
            'lead_hash' => $lead_hash,
            'yandex_metrika_script' => $yandex_metrika_script,
            'facebook_pixel_script' => $facebook_pixel_script,
            'custom_html_code' => $flow_custom_code,
            'google_analitycs_script' => $google_analitycs_script,
            'rating_mail_ru_script' => $rating_mail_ru_script,
            'vk_widget_script' => $vk_widget_script,
        ]);
    }

    /**
     * Исправление данных заказа, если при заказе был введен не валидный номер телефона
     *
     * @todo validation
     *
     * @param PhoneInspector $phone_inspector
     * @return \Illuminate\Http\RedirectResponse
     */
    public function correctOrder(PhoneInspector $phone_inspector)
    {
        /**
         * @var Lead $origin_lead
         */
        $origin_lead = (new Lead())->getByByHash($this->request->input('lead_hash'), ['currency', 'order']);

        $target_geo = (new TargetGeo())->getByHash(
            $this->request->input('target_geo_hash'),
            ['country'],
            (int)$origin_lead['publisher_id']
        );

        $phone = $this->go_request->getPhoneParam();
        if (empty($phone)) {
            $phone = $origin_lead->order['phone'];
        }
        $phone_info = $phone_inspector->checkValid($phone, $target_geo['country']['code']);

        $name = $this->request->get('client');
        if (empty($name)) {
            $name = $origin_lead->order['name'];
        }

        /**
         * @var CodCorrectLeadCreation $correct_lead_creation
         */
        $correct_lead_creation = app(CodCorrectLeadCreation::class);

        // Если лид уже интегрирован - создаем новый
        if ($origin_lead->integrated()) {

            $lead_hash = $correct_lead_creation->handle($origin_lead, $target_geo, $phone_info, $name)['hash'];

            // Если измeнилась гео цель - старый лид закрываем, новый создаем
        } elseif ((int)$origin_lead['target_geo_id'] !== (int)$target_geo['id']) {

            $origin_lead->cancel(Lead::CLOSED_SUBSTATUS_ID);

            $lead_hash = $correct_lead_creation->handle($origin_lead, $target_geo, $phone_info, $name)['hash'];

        } else {

            $order_info = (new Order())->getById($origin_lead['order_id']);

            // Обновление параметра валидности номера телефона
            Lead::find($origin_lead['id'])->update([
                'is_valid_phone' => $phone_info['is_valid'],
            ]);

            // Получаем массив истории изменений параметров заказа
            $order_history = $order_info['history_array'];

            // Добавляем текущие данные в историю правок параметров заказа
            $order_history[] = [
                'date' => time(),
                'name' => $this->request->input('client'),
                'origin_phone' => $phone_info['origin'],
                'phone' => $phone_info['after_processing'],
            ];

            // Изменяем параметры заказа
            Order::find($origin_lead['order_id'])->update([
                'phone' => $phone_info['after_processing'],
                'name' => $this->request->input('client'),
                'is_corrected' => 1,
                'number_type_id' => $phone_info['number_type'],
                'history' => json_encode($order_history)
            ]);
            $lead_hash = $origin_lead['hash'];
        }

        return $this->go_request->getSuccessRedirect($lead_hash);
    }

    public function updateOrderEmail(Lead $lead)
    {
        $email = mb_substr($this->request->input('email', ''), 0, 255);

        $validator = \Validator::make(['email' => $email], ['email' => 'email']);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => trans('go.update_order_data_error')
            ];
        }

        $lead_info = $lead->getByByHash($this->request->input('lead_hash'));

        Order::find($lead_info['order_id'])->update([
            'email' => $email,
        ]);

        return [
            'status' => 'ok',
            'message' => trans('go.update_order_data_success')
        ];
    }

    public function updateOrderAddress(Request $request, Lead $lead)
    {
        $address = mb_substr($request->input('address', ''), 0, 255);

        $validator = \Validator::make(['address' => $address], ['address' => 'string']);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => trans('go.update_order_data_error')
            ];
        }

        $lead_info = $lead->getByByHash($this->request->input('lead_hash'));

        Order::find($lead_info['order_id'])->update([
            'address' => $address,
        ]);

        return [
            'status' => 'ok',
            'message' => trans('go.update_order_data_success')
        ];
    }

    /**
     * Добавление продукта в заказа
     *
     * @todo validation
     *
     * @param Lead $lead
     * @return array
     * @throws \App\Exceptions\Hashids\NotDecodedHashException
     */
    public function addUpsale(Lead $lead)
    {
        $product_hash = $this->request->input('product_hash');
        $lead_hash = $this->request->input('lead_hash');

        if (empty($product_hash) || empty($lead_hash)) {
            return [
                'status' => 'error',
                'message' => 'Incorrect one or more params.'
            ];
        }

        try {
            $lead_info = $lead->getByByHash($lead_hash, ['order', 'integration']);
            // @todo Check that lead belongs to Affninja integrations

            if ($lead_info['status'] !== Lead::NEW) {
                return [
                    'status' => 'error',
                    'message' => 'Lead has been already processed.'
                ];
            }
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return [
                'status' => 'error',
                'message' => 'Unknown lead.'
            ];
        }

        $products = json_decode($lead_info->order->products, true);
        if (!isset($products[$product_hash])) {
            $products[$product_hash] = 0;
        }
        $products[$product_hash]++;

        $lead_info->order->products = json_encode($products);
        $lead_info->order->save();

        if ($lead_info['is_integrated'] === 1) {
            if ($lead_info->integration->title !== Integration::APPROVENINJA) {
                throw new \LogicException('Редактировать заказы можно только для Approveninja интеграции.');
            }
            $job = (new ApproveninjaEditOrder($lead_info['id']))->onQueue(config('queue.app.integration'));
            dispatch($job);
        }

        return ['status' => 'ok'];
    }
}
