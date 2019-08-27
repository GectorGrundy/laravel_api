<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\EmailLog as R;

class EmailLogController extends Controller
{
    use Helpers;

    public function show(R\ShowRequest $request)
    {
        $email_log = EmailLog::where('entity_id', $request->get('entity_id'))
            ->where('entity_type', $request->get('entity_type'))
            ->firstOrFail();

        return $email_log->html;
    }
}
