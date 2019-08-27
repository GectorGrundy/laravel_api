<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Browser;
use App\Http\Requests\Browser as R;

class BrowserController extends Controller
{
    /**
     * @api {GET} /browser.getList browser.getList
     * @apiGroup Browser
     * @apiPermission publisher
     *
     * @apiParam {String} search
     * @apiParam {String[]} [ids[]]
     * @apiSampleRequest /browser.getList
     */
    public function getList(R\GetListRequest $request){

        $search = ($request->input('search', []));
        $browser_ids = $request->input('ids', []);

        $browsers = Browser::search($search)->whereIds($browser_ids)->get();

        return [
            'response' => $browsers,
            'status_code' => 200
        ];
    }
}
