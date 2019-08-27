<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\FlowGroupCreated;
use App\Models\Flow;
use App\Models\FlowGroup;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\FlowGroup as R;

class FlowGroupsController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $flow_group = FlowGroup::create(array_merge($request->all(), [
            'publisher_id' => \Auth::id()
        ]));

        event(new FlowGroupCreated($flow_group));

        return [
            'response' => $flow_group,
            'status_code' => 202
        ];
    }

    public function edit(R\EditRequest $request)
    {
        $flow_group = FlowGroup::where('hash', $request->input('hash'))
            ->first();

        $flow_group->update(array_merge($request->all(), [
            'publisher_id' => \Auth::id()
        ]));

        return $this->response->accepted(null, [
            'response' => $flow_group,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        $flow_group = FlowGroup::where('hash', $request->input('hash'))->first();

        // Сброс группы у потоков, в которых была указана удаляемая группа
        Flow::where('group_id', $flow_group['id'])->update([
            'group_id' => 0
        ]);

        $flow_group->delete();

        return $this->response->accepted(null, [
            'status_code' => 202
        ]);
    }

    public function getList()
    {
        $flow_groups = FlowGroup::publisher(\Auth::user())->latest('id')->get();

        return ['response' => $flow_groups, 'status_code' => 200];
    }
}
