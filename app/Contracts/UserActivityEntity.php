<?php
declare(strict_types=1);

namespace App\Contracts;

/**
 * Интерфейс,
 * который должны реализовать все ивенты,
 * которые отвечают за действия пользователя,
 * которые должны быть в дальнейшем залогированы
 */
interface UserActivityEntity
{
    public function getUserId(): int;
    public function getEntityId(): int;
    public function getEntityType(): string;
}