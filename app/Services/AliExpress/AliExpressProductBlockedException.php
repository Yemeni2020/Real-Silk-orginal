<?php

namespace App\Services\AliExpress;

use RuntimeException;

class AliExpressProductBlockedException extends RuntimeException
{
    public function __construct(public readonly array $reasons)
    {
        parent::__construct('AliExpress product blocked: ' . implode(', ', $reasons));
    }
}
