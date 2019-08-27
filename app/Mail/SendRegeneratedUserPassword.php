<?php
declare(strict_types=1);

namespace App\Mail;

use App\Events\Auth\UserPasswordRegenerated;
use Illuminate\Mail\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class SendRegeneratedUserPassword implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'email';

    public function handle(UserPasswordRegenerated $event)
    {
        $user = $event->user;

        \Mail::send('emails.regenerated_password', [
            'new_password' => $event->new_password
        ], function (Message $m) use ($user) {

            $m->from(config('env.mail_from'), config('env.mail_sender'))
                ->to($user['email'])
                ->subject(trans('emails.password_regenerated_subject', [], 'ru'));
        });
    }
}
