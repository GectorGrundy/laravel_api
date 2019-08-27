<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class Synonimizer
{
    public static $_matches = false;
    public const ROW_DELIMITER = '=>';
    public const VALUE_DELIMITER = '|';
    private $_dictonary = '';
    private static $index = 0;
    private $_parsed = array();

    public function __construct()
    {
        $this->_dictonary = ParserSettings::get('synsDictonary');
        $this->_loadDictonary();
    }

    private function _loadDictonary()
    {
        $fileName = dirname(__FILE__) . '/' . $this->_dictonary;
        if (!file_exists($fileName)) {
            $this->_parsed = array();
            return false;
        }
        $parsed = @file($fileName);
        if (!$parsed) {
            $parsed = array();
        }
        foreach ($parsed as $key => $value) {
            $value = trim($value);
            $strNotValid = (empty($value) || 0 === strpos($value, '#'));
            if ($strNotValid) {
                continue;
            }
            $value = explode(self::ROW_DELIMITER, $value);
            $_key = trim($value[0]);
            $_data = array();
            $delimiterExists = @(strpos($value[1], self::VALUE_DELIMITER) !== false);
            if ($delimiterExists) {
                $_data = $this->_parseSynonims($value, $_data);
            } else {
                $_data = $this->_handleAlias($value);
            }
            $this->_parsed[$_key] = $_data;
        }
    }

    private function _parseSynonims($value, $_data)
    {
        $data = explode(self::VALUE_DELIMITER, $value[1]);
        if (!$data) {
            $data = array();
        }
        foreach ($data as $dataKey => $dataValue) {
            $_data[$dataKey] = trim($dataValue);
        }
        return $_data;
    }

    private function _handleAlias($value)
    {
        $_data = array(@trim($value[1]));
        $isAlias = (strpos($_data[0], '@') === 0);
        if ($isAlias) {
            $_data = $this->_parsed[substr($_data[0], 1)];
            return $_data;
        }
        return $_data;
    }

    public function synonimize($text)
    {
        $needSynonimize = @ParserSettings::get('synonimize');
        if (!isset($needSynonimize) or $needSynonimize == 'off') {
            return $text;
        }
        $text = ' ' . str_replace(array('\r\n', PHP_EOL), "\r\n", $text) . ' ';
        ++self::$index;
        if (!$this->_parsed) {
            $this->_parsed = array();
        }
        foreach ($this->_parsed as $key => $matches) {
            $preg = '#([^\w\d\-])(' . preg_quote($key) . ')([^\w\d\-])#i';
            self::$_matches = &$matches;
            $text = preg_replace_callback($preg, 'Synonimizer::synCallback', $text);
        }
        return $text;
    }

    public static function synCallback($matches)
    {
        $out = $matches[2];
        $matchesNotEmpty = (!empty(self::$_matches));
        if ($matchesNotEmpty) {
            $i = sizeof(self::$_matches) - 1;
            $i = ($i > 0) ? mt_rand(0, $i) : 0;
            $out = self::$_matches[$i];
        }
        return $matches[1] . $out . $matches[3];
    }
}
