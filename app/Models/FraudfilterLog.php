<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudfilterLog extends AbstractEntity
{
    protected $fillable = [
        'campaign_id',
        'api_key',
        'is_safepage',
        'response',
        'X-FF-P',
        'X-FF-REMOTE-ADDR',
        'X-FF-X-FORWARDED-FOR',
        'X-FF-X-REAL-IP',
        'X-FF-DEVICE-STOCK-UA',
        'X-FF-X-OPERAMINI-PHONE-UA',
        'X-FF-HEROKU-APP-DIR',
        'X-FF-X-FB-HTTP-ENGINE',
        'X-FF-X-PURPOSE',
        'X-FF-REQUEST-SCHEME',
        'X-FF-CONTEXT-DOCUMENT-ROOT',
        'X-FF-SCRIPT-FILENAME',
        'X-FF-REQUEST-URI',
        'X-FF-SCRIPT-NAME',
        'X-FF-PHP-SELF',
        'X-FF-REQUEST-TIME-FLOAT',
        'X-FF-COOKIE',
        'X-FF-ACCEPT-ENCODING',
        'X-FF-ACCEPT-LANGUAGE',
        'X-FF-CF-CONNECTING-IP',
        'X-FF-INCAP-CLIENT-IP',
        'X-FF-QUERY-STRING',
        'X-FF-X-FORWARDED-FOR',
        'X-FF-ACCEPT',
        'X-FF-X-WAP-PROFILE',
        'X-FF-PROFILE',
        'X-FF-WAP-PROFILE',
        'X-FF-REFERER',
        'X-FF-HOST',
        'X-FF-VIA',
        'X-FF-CONNECTION',
        'X-FF-X-REQUESTED-WITH',
        'User-Agent',
        'X-FF-HOST-ORDER',
    ];

}
