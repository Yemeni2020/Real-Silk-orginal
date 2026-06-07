<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProductPreview;
use RuntimeException;

class AliExpressCatalogProductBlockedException extends RuntimeException
{
    public function __construct(public readonly AliExpressProductPreview $preview, public readonly array $reasons)
    {
        parent::__construct('AliExpress product blocked: ' . implode(', ', $reasons));
    }
}
