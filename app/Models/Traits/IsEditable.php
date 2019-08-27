<?php
declare(strict_types=1);

namespace App\Models\Traits;

trait IsEditable
{
    public function isEditable(): bool
    {
        return $this->getAttribute('is_editable') === 1;
    }
}
