<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Exceptions\Hashids\NotDecodedHashException;

trait HashDecoder
{
    /**
     * Получение идентификатора хэшу
     *
     * @param string $hash
     * @return int
     *
     * @throws \App\Exceptions\Hashids\NotDecodedHashException
     */
    public function getIdByHash(string $hash): int
    {
        $decoded_data = \Hashids::decode($hash);
        if (!isset($decoded_data[0])) {
            throw new NotDecodedHashException("Could not decode hash {$hash} in" . __CLASS__);
        }

        return (int)$decoded_data[0];
    }
}