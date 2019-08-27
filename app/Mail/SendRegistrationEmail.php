<?php
declare(strict_types=1);

namespace App\Mail;

use App\Events\UserRegistered;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\SerializesModels;

class SendRegistrationEmail implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'email';

    public function __construct()
    {
        $this->queue = config('queue.app.email');
    }

    public function handle(UserRegistered $event)
    {
        $user = $event->user;

        $mail = renderEmailView('emails.registration.full', ['inputs' => $event->request]);

        \Mail::send([], [], function (Message $m) use ($mail, $user) {

            $m->from(config('env.mail_from'), config('env.mail_sender'))
                ->to($user['email'])
                ->subject(trans('publishers.registration_subject'))
                ->setBody($mail, 'text/html');
        });

        EmailLog::create([
            'entity_id' => $user['id'],
            'entity_type' => 'user',
            'type' => 'registration',
            'html' => $mail
        ]);
    }
}
