<?php
declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Mail;
use App\Models\EmailLog;
use Illuminate\Mail\{
    Message
};
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @todo Нереально использовать job с таким кол-вом параметров в конструкторе
 */
class EmailSender implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    private $receiver_email;
    private $receiver_name;
    private $mail_from;
    private $mail_sender;
    private $mail_driver;
    private $locale_code;
    private $template;
    private $subject;
    private $template_data;
    private $extra;
    private $entity_id;
    private $entity_type;
    private $email_type;
    private $is_write_log;

    public function __construct(
        string $template,
        array $template_data,
        string $subject,
        string $receiver_email,
        string $receiver_name,
        string $mail_from,
        string $mail_sender,
        string $mail_driver,
        array $extra,
        string $locale_code,
        bool $is_write_log,
        int $entity_id = 0,
        string $entity_type = '',
        string $email_type = ''
    )
    {
        $this->template = $template;
        $this->template_data = $template_data;
        $this->subject = $subject;
        $this->receiver_email = $receiver_email;
        $this->receiver_name = $receiver_name;
        $this->mail_from = $mail_from;
        $this->mail_sender = $mail_sender;
        $this->mail_driver = $mail_driver;
        $this->extra = $extra;
        $this->locale_code = $locale_code;
        $this->is_write_log = $is_write_log;
        $this->entity_id = $entity_id;
        $this->entity_type = $entity_type;
        $this->email_type = $email_type;
    }

    public function handle(Application $app)
    {
        $this->setEmailConfig();
        $app->setLocale($this->locale_code);

        list($mail_from, $mail_sender, $receiver_email, $receiver_name, $subject) = [
            $this->mail_from,
            $this->mail_sender,
            $this->receiver_email,
            $this->receiver_name,
            $this->subject,
        ];

        $compiled_template = renderEmailView($this->template, $this->template_data);
        Mail::send([], [], function (Message $m) use (
            $mail_from, $mail_sender, $receiver_email, $receiver_name, $subject, $compiled_template
        ) {
            $m->from($mail_from, $mail_sender)->to($receiver_email, $receiver_name)->subject($subject);
            $m->setBody($compiled_template, 'text/html');
        }
        );

        if ($this->is_write_log) {
            EmailLog::create([
                'entity_id' => $this->entity_id,
                'entity_type' => $this->entity_type,
                'type' => $this->email_type,
                'html' => $compiled_template
            ]);
        }
    }

    private function setEmailConfig()
    {
        config([
            'mail.driver' => $this->mail_driver,
            'mail.from' => $this->mail_from,
            'mail.sender' => $this->mail_sender,
            'mail.encryption' => 'tls',
        ]);

        if ($this->mail_driver === 'mailgun') {
            config([
                'services.mailgun.domain' => $this->extra['MAILGUN_DOMAIN'] ?? config('services.mailgun.domain'),
                'services.mailgun.secret' => $this->extra['MAILGUN_SECRET'] ?? config('services.mailgun.secret')
            ]);
        }
    }
}
