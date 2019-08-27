<?php
declare(strict_types=1);

namespace App\Support;

use App\Models\EmailIntegration;

trait EmailConfig
{
    public function setUpEmailConfig(?EmailIntegration $email_integration)
    {
        if (\is_null($email_integration)) {
            return;
        }

        config([
            'mail.driver' => $email_integration->mail_driver,
            'mail.from' => $email_integration->mail_from,
            'mail.sender' => $email_integration->mail_sender,
            'mail.encryption' => 'tls',
        ]);

        if ($email_integration->mail_driver === 'mailgun') {
            $domain = $email_integration->extra_array['MAILGUN_DOMAIN'] ?? config('services.mailgun.domain');
            $secret = $email_integration->extra_array['MAILGUN_SECRET'] ?? config('services.mailgun.secret');

            config([
                'services.mailgun.domain' => $domain,
                'services.mailgun.secret' => $secret,
            ]);
        }
    }
}
