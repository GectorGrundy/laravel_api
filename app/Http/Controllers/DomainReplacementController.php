<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Hashids;
use Dingo\Api\Routing\Helpers;
use App\Models\DomainReplacement;
use App\Http\Requests\DomainReplacement as R;

class DomainReplacementController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /domain_replacements.sync domain_replacements.sync
     * @apiGroup DomainReplacement
     * @apiPermission publisher
     *
     * @apiParamExample {json} Request-Example:
     * { "domain_hash": "vW0vR01w", "replacements": [
     *  {"from": "text to replace", "to": "replacement text"}, {"from": "text1 to replace", "to": "replacement text1"}
     * ]}
     */
    public function sync(R\SyncRequest $request)
    {
        $replacements = $request->input('replacements', []);
        $domain_id = Hashids::decode($request->input('domain_hash'))[0];

        DomainReplacement::where('domain_id', $domain_id)->delete();

        foreach ($replacements as $replacement) {
            DomainReplacement::create([
                'from' => $replacement['from'],
                'to' => $replacement['to'],
                'domain_id' => $domain_id,
            ]);
        }

        return $this->response->accepted(null, [
            'message' => trans('domain_replacements.on_sync_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /domain_replacements.getList domain_replacements.getList
     * @apiGroup DomainReplacement
     * @apiPermission publisher
     * @apiParam {String} domain_hash
     * @apiSampleRequest /domain_replacements.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $domain_id = Hashids::decode($request->input('domain_hash'))[0];

        $replacements = DomainReplacement::where('domain_id', $domain_id)->get();

        return [
            'response' => $replacements,
            'status_code' => 200
        ];
    }
}
