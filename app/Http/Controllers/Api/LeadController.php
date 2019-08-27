<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use Dingo\Api\Routing\Helpers;
use App\Services\PublisherApiDataContainer;
use App\Strategies\LeadCreation\ApiLeadCreation;
use App\Http\Requests\Api\Lead as R;
use App\Http\Controllers\Controller;

class LeadController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        /**
         * @var ApiLeadCreation $strategy
         */
        $strategy = app(ApiLeadCreation::class);
        $lead = $strategy->handle($request);

        return $this->response->accepted(null, [
            'response' => [
                'lead_hash' => $lead['hash']
            ],
            'status_code' => 202
        ]);
    }

    public function getInfo(R\GetInfoRequest $request, PublisherApiDataContainer $data_container)
    {
        $hashes = $request->get('hashes', '');
        $lead_hashes = explode(',', $hashes);

        $leads = Lead::select('hash', 'status')
            ->wherePublisherIds([$data_container->getPublisher()['id']])
            ->whereIn('hash', $lead_hashes)
            ->get();

        $response = [];
        if (null !== $leads) {
            foreach ($leads AS $lead) {
                // Так сделано через $appends поля модели
                $lead = collect($lead->toArray());
                $response[] = $lead->only('hash', 'status');
            }
        }

        return json_encode([
            'response' => $response,
            'status_code' => 200
        ]);
    }

    public function getList(R\GetListRequest $request, PublisherApiDataContainer $data_container)
    {
        $date_start = date('Y-m-d H:i:s', $request->get('date_start'));
        $date_end = date('Y-m-d H:i:s', $request->get('date_end'));

        $leads = Lead::where('publisher_id', $data_container->getPublisher()['id'])
            ->createdBetweenDates($date_start, $date_end)
            ->get();

        $response = [];
        if (null !== $leads) {
            foreach ($leads AS $lead) {
                // Так сделано через $appends поля модели
                $lead = collect($lead->toArray());
                $response[] = $lead->only('hash', 'status');
            }
        }

        return json_encode([
            'response' => $response,
            'status_code' => 200
        ]);
    }
}
