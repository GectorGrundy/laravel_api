<?php
declare(strict_types=1);

namespace App\Support;

class LandingCallbackOperator
{
    private const MAN1_CLASS = 'affcb__manager-man1';
    private const MAN2_CLASS = 'affcb__manager-man2';
    private const MAN3_CLASS = 'affcb__manager-man3';
    private const WOMAN1_CLASS = 'affcb__manager-woman1';
    private const WOMAN2_CLASS = 'affcb__manager-woman2';
    private const WOMAN3_CLASS = 'affcb__manager-woman3';
    private const WOMAN1_TH_CLASS = 'affcb__manager-woman1-th';

    public static function getClassByLocaleCode(string $locale_code): string
    {
        switch (strtolower($locale_code)) {
            case 'sw':
            case 'af':
            case 'bn':
            case 'mg':
                return self::WOMAN1_CLASS;

            case 'es':
            case 'fr':
            case 'ro':
            case 'it':
            case 'pt':
            case 'el':
            case 'mt':
            case 'he':
                return self::WOMAN2_CLASS;

            case 'ru':
            case 'en':
            case 'de':
            case 'pl':
            case 'hu':
            case 'nl':
            case 'bg':
            case 'cs':
            case 'sk':
            case 'sl':
            case 'uk':
            case 'bs':
            case 'sr':
            case 'hr':
            case 'lv':
            case 'lt':
            case 'et':
            case 'no':
            case 'sv':
            case 'da':
            case 'fi':
            case 'ar':
            case 'ur':
            case 'hi':
            case 'fa':
            case 'tr':
            case 'mk':
                return self::WOMAN3_CLASS;

            case 'th':
            case 'vi':
            case 'id':
            case 'zh':
            case 'ko':
            case 'ms':
            case 'ja':
                return self::WOMAN1_TH_CLASS;

            default:
                return self::WOMAN3_CLASS;
        }
    }
}
