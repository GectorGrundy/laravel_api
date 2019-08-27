<?php
declare(strict_types=1);

return [
	'on_login_validate_error' => 'Не удалось войти в систему',

	'on_promo_question_error' => 'Не удалось обработать запрос',
	'on_promo_question_success' => 'Ваш запрос принят!<br> C Вами свяжутся в ближайшее рабочее время.<br> Спасибо!',

	'on_recovery_password_validate_error' => 'Не удалось установить новый пароль',
	'on_login_error' => 'Неверный email или пароль',
	'banned' => 'Ваш аккаунт был заблокирован по причине: :reason',
	'on_enter_in_user_cabinet_error' => 'Ошибка входа в кабинет пользователя',
	'on_return_in_admin_cabinet_error' => 'Ошибка возврата в кабинет пользователя',

	'on_login_success' => 'Аутентификация успешно произведена',
	'on_recovery_password_send_success' => 'Инструкция по восстановлению пароля отправлена на указанный email',
	'on_recovery_password_success' => 'Пароль успешно изменен',

	'email.required' => 'Не указан email',
	'email.exists' => 'Указанный email не зарегестрирован в системе',
	'email.email' => 'Неверный формат email',
	'password.required' => 'Не указан пароль',
	'password.min' => 'Длина пароля должна быть больше 8-ми символов',
	'token.exists' => 'Пароль был изменен ранее. Что-бы сменить еще раз, перейдите в окно входа и нажмите "Забыли пароль?"',
	'recovery_password_link_header' => 'Ссылка для восстановления пароля ' . config('app.name'),
	'forbidden' => 'У вас нет прав для выполнения данной операции',
];