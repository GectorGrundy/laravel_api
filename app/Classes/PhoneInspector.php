<?php
declare(strict_types=1);

namespace App\Classes;

use libphonenumber\{
    PhoneNumberUtil, PhoneNumberFormat, NumberParseException
};

class PhoneInspector
{
    public function checkValid(string $phone, string $country_code): array
    {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();

            $NumberProto = $phoneUtil->parse($phone, $country_code);

            // Получаем тип номера
            $number_type = $phoneUtil->getNumberType($NumberProto);

            // Проверяем номер на валидность
            $is_valid = $phoneUtil->isValidNumber($NumberProto);
            if ($is_valid) {
                // Получаем номер по стандарту E164 (номер с плюсом и кодом страны)
                $valid_number = $phoneUtil->format($NumberProto, PhoneNumberFormat::E164);
            }

        } catch (NumberParseException $e) {
            $is_valid = false;
            $valid_number = $phone;
        }

        return [
            'is_valid' => $is_valid,
            'origin' => $phone,
            'after_processing' => $valid_number ?? $phone,
            'number_type' => $number_type ?? ''
        ];
    }
}
