<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\TicketMessage as R;

class TicketMessageController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /ticket_messages.create ticket_messages.create
     * @apiGroup Ticket
     * @apiPermission publisher
     * @apiPermission admin
     *
     * @apiParam {String} ticket_hash
     * @apiParam {String} message
     * @apiParam {String[]=user.profile} [with[]]
     * @apiSampleRequest /ticket_messages.create
     */
    public function create(R\CreateRequest $request)
    {
        $ticket = Ticket::find(\Hashids::decode($request->input('ticket_hash'))[0]);
        $user = \Auth::user();

        $message = TicketMessage::createNew($user, $ticket, $request->input('message', ''));

        $ticket->updateOnNewMessage($user);

        $with = $request->input('with', []);
        if (\count($with)) {
            $message->load($with);
        }

        return $this->response->accepted(null, [
            'message' => trans('tickets.on_create_message_success'),
            'response' => $message,
            'status_code' => 202
        ]);
    }
}