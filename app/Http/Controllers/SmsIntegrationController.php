<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SmsIntegration;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\SmsIntegration as R;

class SmsIntegrationController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $email_integration = SmsIntegration::create($request->all());

        return [
            'message' => trans('integrations.on_create_success'),
            'response' => $email_integration,
            'status_code' => 202
        ];
    }

    public function edit(R\EditRequest $request)
    {
        SmsIntegration::findOrFail($request->get('id'))->update($request->all());

        $email_integration = SmsIntegration::findOrFail($request->get('id'));

        return $this->response->accepted(null, [
            'message' => trans('integrations.on_edit_success'),
            'response' => $email_integration,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        SmsIntegration::find($request->get('id'))->delete();

        return $this->response->accepted(null, [
            'message' => trans('integrations.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getList(R\GetListRequest $request)
    {
        $integrations = SmsIntegration::latest('id')->get();

        return ['response' => $integrations, 'status_code' => 200];
    }
}
