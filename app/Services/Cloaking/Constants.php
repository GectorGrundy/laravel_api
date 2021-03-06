<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class Constants
{
    public const ITEMS_SELECT = 'input[type="button"], input[type="submit"], button, div, a, span';

    public const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36';

    public static $SPECIAL_CHARS = array('~' => '_a_',
        '#' => '_b_',
        '%' => '_c_',
        '&' => '_d_',
        '*' => '_e_',
        '{' => '_f_',
        '}' => '_g_',
        //'\\' => '_h_',
        ':' => '_j_',
        '[' => '_k_',
        ']' => '_l_',
        '?' => '_m_',
        '+' => '_o_',
        '|' => '_p_',
        '"' => '_q_',
        'amp;' => '');

    public static $IMAGES_TYPES = array('image/jpeg',
        'image/bmp',
        'image/vnd.microsoft',
        'image/tiff'
    );

    public const LOGO_PADDING = 5;

    public const LOGO_FONT_DELIMITER = 35;

    public const SYNONYMS_DEFAULT_SEPARATOR = '=>#SEP#|';

    public const NOT_CACHED_FILE = 'notCacheUrls';
}