<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\UserGroup as R;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserGroupsController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /user_groups.create user_groups.create
     * @apiGroup UserGroup
     * @apiPermission admin
     * @apiParam {String} title
     * @apiParam {String} description
     * @apiParam {String} color e.g.: fff, e3e3e3, f0000f
     * @apiParam {Numeric[]} [users[]] Users in groups
     *
     * @apiSampleRequest /user_groups.create
     */
    public function create(R\CreateRequest $request)
    {
        $user_group = UserGroup::create($request->all());

        $user_ids = $request->input('users', []);
        foreach ($user_ids as $id) {
            $user = User::find($id);
            $user->group()->associate($user_group);
            $user->save();
        }

        $user_group->load('users');

        return $this->response->accepted(null, [
            'message' => trans('user_groups.on_create_success'),
            'response' => $user_group,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /user_groups.edit user_groups.edit
     * @apiGroup UserGroup
     * @apiPermission admin
     * @apiParam {Number} id ID of user group to update.
     * @apiParam {String} title
     * @apiParam {String} description
     * @apiParam {String} color e.g.: fff, e3e3e3, f0000f
     * @apiParam {Numeric[]} [users[]] Users in groups
     *
     * @apiSampleRequest /user_groups.edit
     */
    public function edit(R\EditRequest $request)
    {
        $group_id = $request->input('id');
        $user_group = UserGroup::find($group_id);

        $user_group->update($request->all());

        $user_ids = $request->input('users', []);
        foreach ($user_ids as $id) {
            $user = User::find($id);
            $user->group()->associate($user_group);
            $user->save();
        }

        // Reset group id for another users
        User::where('group_id', $group_id)->whereNotIn('id', $user_ids)->update([
            'group_id' => UserGroup::DEFAULT_ID
        ]);

        $user_group->load('users');

        return $this->response->accepted(null, [
            'message' => trans('user_groups.on_edit_success'),
            'response' => $user_group,
            'status_code' => 202
        ]);
    }

    /**
     * @api {DELETE} /user_groups.delete user_groups.delete
     * @apiGroup UserGroup
     * @apiPermission admin
     * @apiParam {Number} id ID of user group to delete.
     *
     * @apiSampleRequest /user_groups.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        $group_id = $request->input('id');
        $user_group = UserGroup::find($group_id);

        // Disassociate users from deleting group
        User::where('group_id', $group_id)->update(['group_id' => 0]);

        $user_group->delete();

        return $this->response->accepted(null, [
            'message' => trans('user_groups.on_delete_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /user_groups.getList user_groups.getList
     * @apiGroup UserGroup
     * @apiPermission admin
     * @apiParam {String[]=users} [with[]]
     * @apiSampleRequest /user_groups.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $user_groups = UserGroup::with($request->input('with', []))->get();

        return [
            'response' => $user_groups,
            'status_code' => 200
        ];
    }

    /**
     * @api {GET} /user_groups.getById user_groups.getById
     * @apiGroup UserGroup
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiParam {String[]=users} [with[]]
     * @apiSampleRequest /user_groups.getById
     */
    public function getById(R\GetByIdRequest $request)
    {
        try {
            $group = UserGroup::with($request->input('with', []))->findOrFail((int)$request->input('id'));
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound(trans('user_groups.on_get_error'));
        }

        return [
            'response' => $group,
            'status_code' => 200
        ];
    }
}
