<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OsPlatform;
use App\Http\Requests\OsPlatform as R;

class OsPlatformController  extends Controller
{
    /**
     * @api {GET} /os_platform.getList os_platform.getList
     * @apiGroup OsPlatform
     * @apiPermission publisher
     *
     * @apiParam {String} search
     * @apiParam {String[]} [ids[]]
     *
     * @apiSampleRequest /os_platform.getList
     */
    public function getList(R\GetListRequest $request){

        $search = ($request->input('search', []));
        $ids = $request->input('ids', []);

        $os_platforms = OsPlatform::search($search)->whereIds( $ids)->get();

        return [
            'response' => $os_platforms,
            'status_code' => 200
        ];
    }
}
