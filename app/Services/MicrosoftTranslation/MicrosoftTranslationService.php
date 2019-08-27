<?php
declare(strict_types=1);

namespace App\Services\MicrosoftTranslation;

/**
 * Класс обертка для использования MS Azure сервиса переводов текстов
 */
class MicrosoftTranslationService
{
    public const CLIENT_SECRET = '3fe825d587d443208c74516cfd5d6e5f';

    public static function translate(string $from_lang, string $to_lang, string $text)
    {
        $token = (new AccessTokenAuthentication())->getToken(self::CLIENT_SECRET);

        $url = 'http://api.microsofttranslator.com/V2/Http.svc/GetTranslations?'
            . http_build_query([
                'from' => $from_lang,
                'to' => $to_lang,
                'maxTranslations' => 1,
                'text' => $text,
                'contentType' => 'text/plain',
            ]);

        $auth_header = 'Authorization: Bearer ' . $token;
        $response = (new HTTPTranslator())->curlRequest($url, $auth_header);

        $response_object = simplexml_load_string($response);

        $translations = $response_object->Translations->TranslationMatch;
        if (empty($translations)) {
            throw new \LogicException('Сould not translate phrase for specified lanuages.');
        }

        return $translations[0]->TranslatedText;
    }
}
