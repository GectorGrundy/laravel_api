<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\{
    Currency, Lead, Postback, PostbackOut
};
use Illuminate\Queue\{
    InteractsWithQueue, SerializesModels
};
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use App\Events\Event;
use App\Events\Lead\{
    LeadApproved, LeadCancelled, LeadTrashed
};

class SendPublisherPostback implements ShouldQueue
{
    use SerializesModels;
    use InteractsWithQueue;

    private $event;
    private $lead;

    /**
     * @param LeadApproved|LeadCancelled|LeadTrashed $event
     */
    public function handle(Event $event)
    {
        if (
            \is_null($event->postback_event) ||
            ($event->from_status === Lead::TRASHED && $event->lead->isCancelled()) ||
            ($event->from_status === Lead::CANCELLED && $event->lead->isTrashed())
        ) {
            return;
        }

        $this->event = $event;
        $this->lead = $event->lead->load(['offer', 'flow', 'landing', 'transit']);

        $this->processingOnLeadEvent();
    }

    /**
     * Обработка ивентов: on_lead_add, on_lead_approve, on_lead_cancel
     */
    public function processingOnLeadEvent(): void
    {
        $postbacks = Postback::getListByEvent(
            $this->lead['flow_id'],
            $this->lead['publisher_id'],
            sprintf('on_%s', $this->event->postback_event)
        );

        if (!count($postbacks)) {
            return;
        }

        $event_data = $this->getEventData();

        foreach ($postbacks AS $postback) {
            $this->generatePostback($postback, $event_data);
        }
    }

    public function getEventData(): array
    {
        $currency = (new Currency())->getInfo($this->lead['currency_id']);

        $payout = $this->getPayout();
        $payout_usd = $currency->convert($this->lead['currency_id'], Currency::USD_ID, $payout);

        return [
            'offer_hash' => $this->lead->offer['hash'],
            'flow_hash' => $this->lead->flow['hash'] ?? '',
            'landing_hash' => $this->lead->landing['hash'] ?? '',
            'transit_hash' => $this->lead->transit['hash'] ?? '',
            'clickid' => $this->lead['clickid'],
            'ip' => $this->lead['ip'],
            'data1' => $this->lead['data1'],
            'data2' => $this->lead['data2'],
            'data3' => $this->lead['data3'],
            'data4' => $this->lead['data4'],
            'type' => $this->event->postback_event,
            'currency' => $currency['code'],
            'unixtime' => $this->getUnixtime(),
            'payout' => sprintf('%0.2f', round($payout, 2)),
            'payout_usd' => sprintf('%0.2f', round($payout_usd, 2))
        ];
    }

    public function generatePostback(Postback $postback, array $event_data)
    {
        $postback_url = $this->buildPostbackUrl($postback['url'], $event_data);

        $status = 'success';
        $response_http_code = $this->sendPostbackRequest($postback_url);

        if ($response_http_code >= 400) {

            $status = 'fail';
            $attempts = $this->attempts();
            switch ($attempts) {
                case 1:
                    $this->release(config('env.first_postbackout_delay'));
                    break;

                case 2:
                    $this->release(config('env.second_postbackout_delay'));
                    break;

                case 3:
                    $this->release(config('env.third_postbackout_delay'));
                    break;

                default:
                    throw new TooManyRequestsHttpException();
            }
        }

        PostbackOut::create([
            'lead_id' => $this->lead->id,
            'postback_id' => $postback->id,
            'type' => $this->event->postback_event,
            'url' => $postback_url,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Замена токенов в ссылке постбэка
     *
     * @param $postback_url
     * @param $data
     *
     * @return string
     */
    public function buildPostbackUrl(string $postback_url, array $data): string
    {
        return str_replace([
            '{offer_hash}',
            '{flow_hash}',
            '{landing_hash}',
            '{transit_hash}',
            '{clickid}',
            '{ip}',
            '{data1}',
            '{data2}',
            '{data3}',
            '{data4}',
            '{unixtime}',
            '{type}',
            '{currency}',
            '{payout}',
            '{payout_usd}',
            '{lead_hash}',
        ], [
            $data['offer_hash'],
            $data['flow_hash'],
            $data['landing_hash'],
            $data['transit_hash'],
            $data['clickid'],
            $data['ip'],
            $data['data1'],
            $data['data2'],
            $data['data3'],
            $data['data4'],
            $data['unixtime'],
            $data['type'],
            $data['currency'],
            $data['payout'],
            $data['payout_usd'],
            $this->event->lead['hash']
        ],
            $postback_url
        );
    }

    /**
     * Отправка постбека паблишеру
     *
     * @param $request_url
     * @return int
     */
    public function sendPostbackRequest($request_url): int
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "NinjaBot-Postback/1.0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_exec($ch);

        $info = curl_getinfo($ch);

        curl_close($ch);

        return (int)$info['http_code'];
    }

    private function getPayout(): float
    {
        switch ($this->event->postback_event) {
            case PostbackOut::LEAD_CANCEL:
            case PostbackOut::LEAD_ADD:
                return 0;

            case PostbackOut::LEAD_APPROVE:
                return (float)$this->event->lead['payout'];

            default:
                throw new \LogicException('Неизвестный ивент постбека');
        }
    }

    private function getUnixtime(): int
    {
        switch ($this->event->postback_event) {
            case PostbackOut::LEAD_CANCEL:
            case PostbackOut::LEAD_APPROVE:
                return time();

            case PostbackOut::LEAD_ADD:
                return strtotime($this->event->lead['created_at']);

            default:
                throw new \LogicException('Неизвестный ивент постбека');
        }
    }
}
