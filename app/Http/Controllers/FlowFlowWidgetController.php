<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Flow;
use Dingo\Api\Routing\Helpers;
use App\Models\FlowFlowWidget;
use App\Http\Requests\FlowFlowWidget as R;
use Illuminate\Database\Eloquent\Builder;

class FlowFlowWidgetController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request): \Dingo\Api\Http\Response
    {
        $flow_widget = FlowFlowWidget::create(array_merge($request->all(), [
            'attributes' => json_encode($request->get('attributes'))
        ]));

        return $this->response()->accepted(null, [
            'message' => trans('flow_widget.on_create_success'),
            'response' => $flow_widget,
            'status_code' => 202
        ]);
    }

    public function edit(R\EditRequest $request): \Dingo\Api\Http\Response
    {
        $flow_widget = FlowFlowWidget::find($request->flow_flow_widget_id);

        $db_attr = collect(json_decode($flow_widget['attributes']));
        $request_attr = collect($request->get('attributes'));
        $need_moderate = $flow_widget->flow_widget_id === 3 && $db_attr->diff($request_attr)->count();

        $flow_widget->update([
            'attributes' => json_encode($request->get('attributes')),
            'is_moderated' => !$need_moderate
        ]);

        return $this->response()->accepted(null, [
            'message' => trans('flow_widget.on_edit_success'),
            'response' => $flow_widget,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        FlowFlowWidget::find($request->flow_flow_widget_id)->delete();

        return $this->response()->accepted(null, [
            'message' => trans('flow_widget.on_delete_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /flow_flow_widget.getCustomCodeList flow_flow_widget.getCustomCodeList
     * @apiGroup FlowFlowWidget
     * @apiPermission support
     * @apiSampleRequest /flow_flow_widget.getCustomCodeList
     */
    public function getCustomCodeList()
    {
        $widgets = FlowFlowWidget::with('flow.user')
            ->whereHas('flow', function (Builder $builder) {
                return $builder->where('status', Flow::ACTIVE);
            })
            ->has('flow.user')
            ->where('flow_widget_id', 3)
            ->where('is_moderated', 0)
            ->get();

        return ['response' => $widgets, 'status_code' => 200];
    }

    /**
     * @api {POST} /flow_flow_widget.moderate flow_flow_widget.moderate
     * @apiGroup FlowFlowWidget
     * @apiPermission support
     * @apiParam {String} hash
     * @apiSampleRequest /flow_flow_widget.moderate
     */
    public function moderate(R\ModeratedRequest $request)
    {
        FlowFlowWidget::where('hash', $request->get('hash'))->update(['is_moderated' => 1]);

        return $this->response()->accepted(null, [
            'message' => trans('flow_widget.on_moderate_success'),
            'status_code' => 202
        ]);

    }
}
