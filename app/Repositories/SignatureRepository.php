<?php

namespace App\Repositories;

use App\Contracts\Repositories\SignatureRepositoryInterface;
use App\Models\DeliveryZipCode;
use App\Models\Signatures;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SignatureRepository implements SignatureRepositoryInterface
{
    public function __construct(
        private readonly Signatures $signature,
    )
    {

    }



    public function add(array $data): string|object
    {
        return $this->signature->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
      
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
      
    }

    public function getListWhere(array $orderBy=[], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null):  Collection|LengthAwarePaginator
    {
       
    }

    public function update(string $id, array $data): bool
    {
        return $this->signature->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->signature->where($params)->delete();
        return true;
    }
}
