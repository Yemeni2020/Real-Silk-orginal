<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SignatureRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $status
     * @param array $relations
     * @param int $paginateBy
     * @return Collection|array|LengthAwarePaginator
     */
}
