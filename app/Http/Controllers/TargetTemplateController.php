<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Locale;
use App\Models\TargetTemplate;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\TargetTemplate as R;

class TargetTemplateController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /target_template.create target_template.create
     * @apiGroup TargetTemplate
     * @apiPermission admin
     * @apiParam {String} title
     * @apiParam {String} title_en
     * @apiSampleRequest /target_template.create
     */
    public function create(R\CreateRequest $request)
    {
        $template = TargetTemplate::create($request->all());

        $template->syncTitleTranslations([[
            'locale_id' => Locale::EN,
            'title' => $request->input('title_en')
        ]]);

        $template = TargetTemplate::with(['translations'])->find($template['id']);

        return $this->response->accepted(null, [
            'message' => trans('target_templates.on_create_success'),
            'response' => $template,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /target_template.edit target_template.edit
     * @apiGroup TargetTemplate
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiParam {String} title
     * @apiParam {String} title_en
     * @apiSampleRequest /target_template.edit
     */
    public function edit(R\EditRequest $request)
    {
        $template = TargetTemplate::find($request->get('id'));
        $template->update($request->all());

        $template->syncTitleTranslations([[
            'locale_id' => Locale::EN,
            'title' => $request->input('title_en')
        ]]);

        $template = TargetTemplate::with(['translations'])->find($template['id']);

        return $this->response->accepted(null, [
            'message' => trans('target_templates.on_edit_success'),
            'response' => $template,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /target_template.getList target_template.getList
     * @apiGroup TargetTemplate
     * @apiPermission admin
     * @apiParam {String[]=translations} [with[]]
     * @apiSampleRequest /target_template.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $tempaltes = TargetTemplate::with($request->input('with', []))->latest('id')->get();

        return ['response' => $tempaltes, 'status_code' => 200];
    }
}
