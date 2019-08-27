<?php
declare(strict_types=1);

use App\Services\CloakingService;

if (!function_exists('toDate')) {
    function toDate($datetime)
    {
        return date('Y-m-d', strtotime($datetime));
    }
}

if (!function_exists('toHumanDate')) {
    function toHumanDate($datetime)
    {
        return date('d.m.Y', strtotime($datetime));
    }
}

if (!function_exists('rejectEmpty')) {
    function rejectEmpty(array $array)
    {
        return collect($array)
            ->reject(function ($item) {
                return empty($item);
            })
            ->toArray();
    }
}

if (!function_exists('getIdsByHashes')) {
    function getIdsByHashes(array $hashes = [])
    {
        if (!count($hashes)) {
            return [];
        }

        return collect(
            array_map(function ($hash) {
                return \Hashids::decode($hash)[0] ?? null;
            }, $hashes)
        )
            ->reject(function ($value) {
                return is_null($value);
            })->toArray();
    }
}

if (!function_exists('getIdFromHash')) {
    function getIdFromHash($hash)
    {
        if (is_string($hash)) {
            return \Hashids::decode($hash)[0] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('isApiRequest')) {
    function isApiRequest()
    {
        return isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === config('env.api_domain');
    }
}

if (!function_exists('renderEmailView')) {
    function renderEmailView(string $view, array $view_data = [])
    {
        return trim(preg_replace('/\s\s+/', ' ', view($view, $view_data)->render()));
    }
}

if (!function_exists('getQuesyStringFromArray')) {

    function getQuesyStringFromArray(array $params): string
    {
        $query_string = '';
        if (count($params) > 0) {
            $query_string = '?' . http_build_query($params);
        }

        return $query_string;
    }
}

if (!function_exists('redirectWithLandingHash')) {

    /**
     * Редирект на роут с идентификатором лендинга
     *
     * @param string $route_name
     * @param array $params
     * @param bool $hard_redirect // Если false - метод возвращает ссылку
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    function redirectWithLandingHash(string $route_name, array $params = [], bool $hard_redirect = true)
    {
        $data_container = app(\App\Http\GoDataContainer::class);
        $redirect = redirect()->route($route_name, $params);

        $query_string = strpos($redirect->getTargetUrl(), '?') === false ? '?' : '&';
        $query_string .= http_build_query([
            CloakingService::FOREIGN_PARAM => $data_container->getLanding()['hash']
        ]);

        $redirect = $redirect->setTargetUrl($redirect->getTargetUrl() . $query_string);

        if ($hard_redirect) {
            return $redirect;
        }

        return $redirect->getTargetUrl();
    }
}

if (!function_exists('renameFileInPath')) {

    function renameFileInPath($filepath, $new_filename)
    {
        $filename = \File::name($filepath);
        return str_replace($filename, $new_filename, $filepath);
    }
}

if (!function_exists('generateRandomFilename')) {
    /**
     * Генерирует случайное имя файла и проверяет на существование такого в директории.
     * Есть 10 попыток сгенерировать уникальное имя файла в директории, после чего бросается исплючение
     *
     * @param $filepath
     * @param string $file_extension
     *
     * @return string
     */
    function generateRandomFilename($filepath, $file_extension = 'png'): string
    {
        $filename = '';
        $iteration = 0;
        while (true) {
            if ($iteration > 10) {
                throw new \LogicException('Too many attemps to generate unique filename.');
            }

            $filename = str_random() . '.' . $file_extension;
            if (!file_exists($filepath . $filename)) {
                break;
            }
            $iteration++;
        }

        return $filename;
    }
}

if (!function_exists('paginationOffset')) {

    function paginationOffset($page, $per_page)
    {
        return ($page - 1) * $per_page;
    }
}
if (!function_exists('allEntitiesLoaded')) {

    function allEntitiesLoaded($total_entities, $page, $per_page)
    {
        return ($page * $per_page) >= $total_entities;
    }
}

if (!function_exists('hardRedirect')) {

    function hardRedirect($url)
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('getSelfUrl')) {
    function getSelfUrl()
    {
        $s = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0,
            strpos($_SERVER['SERVER_PROTOCOL'], '/'));

        if (!empty($_SERVER["HTTPS"])) {
            $s .= ($_SERVER["HTTPS"] === "on") ? "s" : "";
        }

        $s .= '://' . $_SERVER['HTTP_HOST'];

        if (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] !== 80) {
            $s .= ':' . $_SERVER['SERVER_PORT'];
        }

        $s .= dirname($_SERVER['SCRIPT_NAME']);

        return $s;
    }
}

if (!function_exists('dbd')) {
    /**
     * Showing all database queries.
     *
     * @param null|\Illuminate\Console\Command|\Psr\Log\LoggerInterface $channel
     */
    function dbd($channel = null)
    {
        static $initialized;
        if ($initialized) {
            return;
        }
        app('db')->listen(function ($sql) use ($channel) {
            foreach ($sql->bindings as $i => $binding) {
                $sql->bindings[$i] = is_string($binding) ? "'$binding'" : (string)$binding;
            }

            $time = $sql->time / 1000;
            $query = "[{$time}s] ";
            $query .= str_replace(['%', '?'], ['%%', '%s'], $sql->sql);
            $query = vsprintf($query, $sql->bindings);

            if (null === $channel) {
                dump($query);
            } elseif ($channel instanceof \Illuminate\Console\Command) {
                $channel->info($query);
            } elseif ($channel instanceof \Psr\Log\LoggerInterface) {
                $channel->info($query . "\n");
            }
        });
        $initialized = true;
    }
}

if (!function_exists('isLocalEnvironment')) {

    /**
     * @return bool
     */
    function isLocalEnvironment()
    {
        return app()->isLocal();
    }
}

if (!function_exists('hidePartOfString')) {

    /**
     * Функция для замены части строки нужными символами
     *
     * @param $str // Срока замены
     * @param int $length // Кол-во символов замены
     * @param string $symbol // Символ, которым нужно заменять
     * @param string $start_point // Откуда начинать менять строку - с начала или конца
     * @return string
     */
    function hidePartOfString($str, $length = 0, $symbol = '*', $start_point = 'end')
    {
        $str_length = mb_strlen($str);

        if ($str_length < 1) {
            return $str;
        }

        // Если не задано сколько символов нужно заменять - меняем всю строку
        if (!$length) {
            $length = $str_length;
        }

        // Если длина строки меньше заданой - меняем всю строку
        if ($str_length <= $length) {
            return str_repeat($symbol, $length);
        }

        // Начинаем менять с начала строки
        if ($start_point === 'start') {
            return str_repeat($symbol, $length) . mb_substr($str, $length);
        }

        // Начинаем менять с конца строки
        return mb_substr($str, 0, $str_length - $length) . str_repeat($symbol, $length);
    }
}

if (!function_exists('sendIntegrationRequest')) {

    /**
     * Отправка запроса интеграции
     *
     * @param string $url
     * @param string $method
     * @param array| string $params
     * @param array $headers
     * @return array
     */
    function sendIntegrationRequest(string $url, string $method = 'GET', $params = '', array $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, config('app.name'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        curl_close($ch);

        if (is_bool($response)) {
            throw new \LogicException('Could not get result by specified params: ' . print_r([
                    'url' => $url,
                    'method' => $method,
                    'params' => is_array($params) ? http_build_query($params) : $params,
                    'headers' => $headers,
                ], true));
        }

        return [
            'response' => $response,
            'curl_info' => $curl_info,
        ];
    }
}

if (!function_exists('getHumanPrice')) {
    /**
     * Возврат цены пригодной для человеческого глаза
     *
     * @param array $price
     * @param string $currency
     * @return array
     */
    function getHumanPrice($price, $currency)
    {
        switch ($currency) {
            case 'rub':
                return $price . ' ' . getHumanCurrency($currency);

            case 'usd':
                return getHumanCurrency($currency) . $price;

            default:
                return '-';
        }

    }
}

if (!function_exists('getHumanPriceByCurrencyId')) {
    /**
     * Возврат цены пригодной для человеческого глаза
     *
     * @param array $price
     * @param string $currency_id
     * @return string
     */
    function getHumanPriceByCurrencyId($price, $currency_id)
    {
        switch ($currency_id) {
            case 1:// RUB
                return $price . ' ' . getHumanCurrencyByCurrencyId($currency_id);

            case 3:// USD
                return getHumanCurrencyByCurrencyId($currency_id) . $price;

            case 5:// EUR
                return getHumanCurrencyByCurrencyId($currency_id) . $price;

            default:
                return '-';
        }

    }
}

if (!function_exists('getNameUserRole')) {
    /**
     * Возврат названия роли пользователя
     *
     * @param string $user_role
     * @return array
     */
    function getNameUserRole($user_role)
    {
        switch ($user_role) {
            case 'administrator':
                return trans('global::app.administrator');

            case 'publisher':
                return trans('global::app.publisher');

            default:
                return '-';
        }
    }
}

if (!function_exists('getHumanCurrency')) {

    /**
     * Получение обозначения валюты
     *
     * @param string $currency
     * @return array
     */
    function getHumanCurrency($currency)
    {
        switch ($currency) {
            case 'rub':
                return '<span class="rubl">о</span>';

            case 'usd':
                return '$';

            case 'eur':
                return '€';

            default:
                return '-';
        }

    }
}

if (!function_exists('getHumanCurrencyByCurrencyId')) {

    /**
     * Получение обозначения валюты
     *
     * @param string $currency_id
     * @return string
     */
    function getHumanCurrencyByCurrencyId($currency_id)
    {
        switch ($currency_id) {
            case 1:
                return '<span class="rubl">о</span>';

            case 3:
                return '$';

            case 5:
                return '€';

            default:
                return '-';
        }

    }
}

if (!function_exists('getSimpleArrayFromAssociative')) {
    /**
     * Получение необходимых полей ассоциативного массива
     *
     * @param array $associative_array
     * @param string $field
     * @return array
     */
    function getSimpleArrayFromAssociative($associative_array, $field)
    {
        $result_array = [];
        foreach ($associative_array AS $array) {
            $result_array[] = $array[$field];
        }

        return $result_array;
    }
}

if (!function_exists('file_force_contents')) {

    /**
     * Запись файлов с рекурсивным созданием папок
     *  !Внимание, так как файлы могут запрашиваться отдновременно,
     *  возможен вариант создания одной и той же папки одновременно.
     *  Поэтому создание папки и запись файлы обернуты в блоки try catch
     *
     * @param $dir
     * @param $contents
     */
    function file_force_contents($dir, $contents)
    {
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                try {

                    mkdir($dir);

                } catch (Exception $e) {

                }
            }
        }

        try {

            file_put_contents("$dir/$file", $contents);

        } catch (Exception $e) {

        }
    }
}

if (!function_exists('ddMail')) {

    function ddMail()
    {
        \Mail::raw(print_r(func_get_args(), TRUE), function ($message) {
            $message->from('us@example.com', 'Laravel');
            $message->to('foo@example.com')->cc('bar@example.com');
        });
    }
}

if (!function_exists('sendHttpRequest')) {

    /**
     * Отправка HTTP запроса
     *
     * @param $method
     * @param $url
     * @param $params
     * @param $headers
     * @return mixed
     */
    function sendHttpRequest($method, $url, array $params = [], array $headers = [])
    {
        return Requests::request($url, $headers, $params, $method)->body;
    }
}

if (!function_exists('getUserCabinetUrl')) {

    /**
     * Получение ссылки на кабинет пользователя
     *
     * @param $user_role
     * @return string
     */
    function getUserCabinetUrl($user_role)
    {
        switch ($user_role) {
            case \App\Models\User::PUBLISHER:
                return getRequestScheme() . 'my.' . config('env.main_domain');

            case \App\Models\User::ADMINISTRATOR:
                return getRequestScheme() . 'control.' . config('env.main_domain');

            case \App\Models\User::ADVERTISER:
                return getRequestScheme() . 'office.' . config('env.main_domain');

            case \App\Models\User::SUPPORT:
                return getRequestScheme() . 'support.' . config('env.main_domain');

            case \App\Models\User::MANAGER:
                return getRequestScheme() . 'manager.' . config('env.main_domain');

            default:
                throw new InvalidArgumentException();
        }
    }
}

if (!function_exists('getRequestScheme')) {
    function getRequestScheme()
    {
        return Request::getScheme() . '://';
    }
}

if (!function_exists('cidrToRange')) {
    function cidrToRange($cidr)
    {
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);

        return $range;
    }
}

if (!function_exists('getRandomNumber')) {

    function getRandomNumber($min, $max)
    {
        return $min + rand(0, getrandmax()) / getrandmax() * abs($max - $min);
    }
}

if (!function_exists('getRandomCode')) {
    /**
     * Получение рандомной строки заданой длины
     *
     * @param int $len
     * @return string
     */
    function getRandomCode($len = 6)
    {
        $len = (int)$len;
        $array = array(
            'a', 'b', 'c',
            'd', 'e', 'f',
            'g', 'h', 'j',
            'k', 'm', 'n',
            'p', 'q', 'r',
            's', 't', 'u',
            'v', 'w', 'x',
            'y', 'z', '1',
            '2', '3', '4',
            '5', '6', '7',
            '8', '9', '0',
            'A', 'B', 'C',
            'D', 'E', 'F',
            'G', 'H', 'J',
            'K', 'M', 'N',
            'P', 'Q', 'R',
            'S', 'T', 'U',
            'V', 'W', 'X',
            'Y', 'Z'
        );
        shuffle($array);
        $gen_code = '';
        for ($in = 0; $in < $len; $in++) {
            $rand_let = (rand(1, 50) + 1);
            $gen_code .= $array[$rand_let];
        }
        return $gen_code;
    }
}

if (!function_exists('getIpInfo')) {

    /**
     * Получение данных по IP адрессу
     *
     * @param $ip
     * @return mixed
     * @throws Exception
     */
    function getIpInfo($ip)
    {
        $reader = new \GeoIp2\Database\Reader(config('env.path_to_geoip_lite_file'));
        $record = $reader->city($ip);

        return (array)$record->raw;
    }
}

if (!function_exists('getIpIsp')) {

    /**
     * Получение ISP ip адресса
     *
     * @param $ip
     * @return mixed
     * @throws Exception
     */
    function getIpIsp($ip)
    {
        $giisp = geoip_open(config('env.path_to_geoip_isp_file'), GEOIP_STANDARD);
        $isp = geoip_org_by_addr($giisp, $ip);
        geoip_close($giisp);

        return $isp;
    }
}

if (!function_exists('getCountryNameByIp')) {

    /**
     * Получение названия страны по IP
     *
     * @param $ip
     * @return mixed
     * @throws Exception
     */
    function getCountryNameByIp($ip)
    {
        $gi = geoip_open(config('env.path_to_geoip_file'), GEOIP_STANDARD);

        $country_name = geoip_country_name_by_addr($gi, $ip);

        geoip_close($gi);

        if (empty($country_name)) {
            throw new \App\Exceptions\Geoip\NotDetarmineCountryName();
        }

        return $country_name;
    }
}

if (!function_exists('addErrorsToValidator')) {
    function addErrorsToValidator($failed_validator, $form_request)
    {
        foreach ($failed_validator->errors()->messages() as $error_field => $error) {
            $form_request->errors()->add($error_field, $error[0]);
        }
    }
}

if (!function_exists('addAccessErrorToValidator')) {

    function addAccessErrorToValidator($validator, $flows)
    {
        $limit = 5;

        foreach ($flows->take($limit) as $flow) {
            $validator->errors()->add('flow_exists', "{$flow['user']['email']}, {$flow['title']}");
        }

        if ($flows->count() > $limit) {
            $validator->errors()->add('flow_exists', trans('messages.ant_yet_flows', [
                'count' => $flows->count() - $limit,
            ]));
        }
    }
}