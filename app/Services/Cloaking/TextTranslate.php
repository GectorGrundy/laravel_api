<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class TextTranslate
{
    public const DEFAULT_ADAPTER = NotTranslateAdapter::class;
    public const SELECTOR_FOR_TRANSLATE_ITEMS = 'text';
    private static $_instance;

    public function translate($string, $adapter = null)
    {
        return self::getInstance($adapter)->translate($string);
    }

    public static function getInstance($adapter = null)
    {
        $settings = new ParserSettings();
        if (!$adapter) {
            $adapter = $settings->get('translateAdapter');
        }
        if (!isset($adapter)) {
            $adapter = self::DEFAULT_ADAPTER;
        }

        $obj = new $adapter();

        $obj->setSource(@ParserSettings::get('translateSource'));
        $obj->setTarget(@ParserSettings::get('translateTarget'));
        return $obj;
    }
}
