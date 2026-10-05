<?php

namespace App\Services\Randi;

use RuntimeException;

class RandiConflict extends RuntimeException
{
    public function __construct(public readonly string $reason, public readonly int $status = 409)
    {
        parent::__construct($reason);
    }
}
