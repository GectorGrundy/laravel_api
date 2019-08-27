<?php

return [
    'on_edit_error' => 'Невозможно изменить реквизит',
    'on_get_list_error' => 'Не удалось получить реквизиты',
    'on_get_list_for_payment_error' => 'Не удалось получить реквизиты',

    'on_edit_success' => 'Реквизиты изменены',

    'user_id.exists' => 'Некорректное значение поля user_id',
    'details.unique' => 'Данный реквизит уже есть в системе',
    'requisite.unique' => 'Данный :payment_system реквизит уже есть в системе',
    'swift.unique' => 'Указанный номер платежной карты уже есть в системе',

    'card_number.incorrect' => 'Неверный формат платежной карты',
    'wmr.incorrect' => 'Неверный формат WMR кошелька',
    'wmz.incorrect' => 'Неверный формат WMZ кошелька',
    'wme.incorrect' => 'Неверный формат WME кошелька',
    'paxum.email' => 'Неверный email в Paxum',
    'epayments.regex' => 'Неверный формат eWallet',
];
