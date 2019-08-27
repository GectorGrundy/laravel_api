<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuthToken;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\AuthToken as R;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /auth_token.getList auth_token.getList
     * @apiGroup AuthToken
     * @apiPermission publisher
     * @apiSampleRequest /auth_token.getList
     */
    public function getList()
    {
        $auth_tokens = AuthToken::whereUser( \Auth::id())->whereNotAdmin()->get();

        return [
            'response' => $auth_tokens,
            'status_code' => 200
        ];
    }

    /**
     * @api {DELETE} /auth_token.deleteByHash auth_token.deleteByHash
     * @apiGroup AuthToken
     * @apiPermission publisher
     * @apiParam {String} hash
     * @apiSampleRequest /auth_token.deleteByHash
     */
    public function deleteByHash(R\DeleteRequest $request)
    {
        AuthToken::where('hash', $request->input('hash'))->delete();

        return $this->response->accepted(null, [
            'message' => trans('auth_token.on_delete_success'),
            'status_code' => 202
        ]);
    }

    /**
     * @api {DELETE} /auth_token.deleteExceptCurrenToken auth_token.deleteExceptCurrenToken
     * @apiGroup AuthToken
     * @apiPermission publisher
     * @apiSampleRequest /auth_token.deleteExceptCurrenToken
     */
    public function deleteExceptCurrenToken(Request $request)
    {
        $auth_tokens = AuthToken::where('user_id', \Auth::id())
            ->where('id', '!=', $request->input('auth_token')['id']);
        $auth_tokens->delete();


        return $this->response->accepted(null, [
            'message' => trans('auth_token.on_delete_except_current_success'),
            'status_code' => 202
        ]);
    }
}
