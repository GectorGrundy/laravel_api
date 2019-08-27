<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Hashids;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Ticket as R;
use App\Models\{
    Ticket, TicketMessage
};

class TicketController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /ticket.create ticket.create
     * @apiGroup Ticket
     * @apiPermission publisher
     *
     * @apiParam {String{..255}} title
     * @apiParam {String} first_message
     * @apiParam {String[]} [with[]] Publisher allowed values: <code>last_message_user.profile</code>
     *
     * @apiSampleRequest /ticket.create
     */
    public function create(R\CreateRequest $request)
    {
        $user = Auth::user();

        $ticket = Ticket::create(array_merge($request->only('title'), [
            'user_id' => $user['id'],
            'last_message_user_id' => $user['id'],
            'last_message_user_type' => $user['role'],
            'status' => Ticket::CREATED,
            'is_read_user' => 1,
            'last_message_at' => Carbon::now(),
        ]));

        TicketMessage::createNew($user, $ticket, $request->input('first_message', ''));

        // Load possible publisher relations
        if ($user->isPublisher() && \count($with = $request->input('with', []))) {
            $ticket->load(collect($with)->intersect(Ticket::$allowed_publisher_relations)->toArray());
        }

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_create_success'),
            'response' => $ticket,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /ticket.getList ticket.getList
     * @apiGroup Ticket
     * @apiPermission publisher
     * @apiPermission admin
     * @apiParam {String[]} [with[]] Admin allowed values: <code>user.group,last_message_user.profile,responsible_user</code><br>
     *                               Other allowed values: <code>last_message_user.profile</code>
     * @apiSampleRequest /ticket.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $user = Auth::user();

        $read_field = $user->isAdmin() ? 'is_read_admin' : 'is_read_user';

        $tickets = Ticket::with($request->input('with', []))
            ->orderByRaw("`{$read_field}`, `last_message_at` DESC")
            ->get();

        return ['response' => $tickets, 'status_code' => 200];
    }

    /**
     * @api {GET} /ticket.getByHash ticket.getByHash
     * @apiGroup Ticket
     * @apiPermission publisher
     * @apiPermission admin
     * @apiParam {String} hash
     * @apiParam {String[]=user,messages.user,messages.user.profile,messages.user.group} [with[]]
     * @apiSampleRequest /ticket.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $ticket = Ticket::with($request->input('with', []))
            ->where('hash', $request->input('hash'))
            ->first();

        if (\is_null($ticket)) {
            $this->response->errorNotFound(trans('tickets.on_get_error'));
            return;
        }

        return ['response' => $ticket, 'status_code' => 200];
    }

    /**
     * @api {POST} /ticket.open ticket.open
     * @apiGroup Ticket
     * @apiPermission admin
     * @apiParam {String} hash
     * @apiSampleRequest /ticket.open
     */
    public function open(R\OpenRequest $request)
    {
        $ticket = Ticket::find(Hashids::decode($request->input('hash'))[0]);

        $ticket->update(['status' => Ticket::PENDING_ANSWER]);

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_open_success'),
            'response' => $ticket,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /ticket.close ticket.close
     * @apiGroup Ticket
     * @apiPermission admin
     * @apiParam {String} hash
     * @apiSampleRequest /ticket.close
     */
    public function close(R\CloseRequest $request)
    {
        $ticket = Ticket::find(Hashids::decode($request->input('hash'))[0]);

        $ticket->update(['status' => Ticket::CLOSED]);

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_close_success'),
            'response' => $ticket,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /ticket.defer ticket.defer
     * @apiGroup Ticket
     * @apiPermission admin
     *
     * @apiParam {String} hash
     * @apiParam {Number} responsible_user_id
     * @apiParam {String} deferred_until_at Date in format <code>Y-m-d H:i:s</code>
     *
     * @apiSampleRequest /ticket.defer
     */
    public function defer(R\DeferRequest $request)
    {
        $ticket = Ticket::find(Hashids::decode($request->input('hash'))[0]);

        $ticket->update([
            'responsible_user_id' => $request->input('responsible_user_id'),
            'deferred_until_at' => $request->input('deferred_until_at'),
            'last_message_at' => Carbon::now()->toDateTimeString(),
            'status' => Ticket::DEFERRED,
        ]);

        $ticket->load('responsible_user');

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_defer_success'),
            'response' => $ticket,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /ticket.markAsRead ticket.markAsRead
     * @apiGroup Ticket
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {String} hash
     * @apiSampleRequest /ticket.markAsRead
     */
    public function markAsRead(R\MarkAsRequest $request)
    {
        $ticket = Ticket::find(Hashids::decode($request->input('hash'))[0]);

        $ticket->update([
            Ticket::getReadFieldByUserRole(Auth::user()['role']) => 1,
        ]);

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_mark_as_read_success'),
            'response' => $ticket,
            'status_code' => 202
        ]);
    }
}
