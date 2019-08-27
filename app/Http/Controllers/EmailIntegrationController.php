<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailIntegration;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\EmailIntegration as R;

class EmailIntegrationController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $email_integration = EmailIntegration::create($request->all());

        return [
            'message' => trans('integrations.on_create_success'),
            'response' => $email_integration,
            'status_code' => 202
        ];
    }

    public function edit(R\EditRequest $request)
    {
        $email_integration = EmailIntegration::findOrFail($request->get('id'));

        $email_integration->update($request->all());

        return $this->response->accepted(null, [
            'message' => trans('integrations.on_edit_success'),
            'response' => $email_integration,
            'status_code' => 202
        ]);
    }
}
