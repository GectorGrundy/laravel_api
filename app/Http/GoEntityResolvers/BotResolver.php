<?php
declare(strict_types=1);

namespace App\Http\GoEntityResolvers;

use App\Classes\BotInspector;
use App\Http\GoDataContainer;
use Illuminate\Http\Request;
use App\Models\Flow;

/**
 * Проверка на бота
 */
class BotResolver
{
    private $bot_inspector;
    private $data_container;

    public function __construct(BotInspector $bot_inspector, GoDataContainer $data_container)
    {
        $this->bot_inspector = $bot_inspector;
        $this->data_container = $data_container;
    }

    /**
     * Если у потока установлена настройка "Отсеивать ботов в статистике" - проверяем, что это не бот
     *
     * @param Flow $flow
     * @param array $visitor
     */
    public function resolve(Flow $flow, array $visitor): void
    {
        $is_bot = false;
        if ((int)$flow['is_detect_bot'] === 1) {
            $is_bot = $this->bot_inspector->isBot($visitor['user_agent'], $visitor['ip'], $_SERVER);
        }

        $this->data_container->setIsBot($is_bot);
    }
}