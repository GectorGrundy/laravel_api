Перейдите по указанной ссылке для смены пароля:
<a href="{{ 'http://' . env('MAIN_DOMAIN') . '/password/reset/'.$token }}">
    {{ 'http://' . env('MAIN_DOMAIN') . '/password/reset/'.$token }}
</a>
