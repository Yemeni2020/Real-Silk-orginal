<?php

namespace App\Repositories;

use App\Contracts\Repositories\PostSeoRepositoryInterface;
use App\Models\PostSeo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PostSeoRepository implements PostSeoRepositoryInterface
{
    public function __construct(
        private readonly PostSeo $postSeo,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->postSeo->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->postSeo->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        // TODO: Implement getList() method.
    }

    public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->postSeo
            ->with($relations)
            ->when(isset($filters['post_id']), function ($query) use ($filters) {
                return $query->where(['post_id' => $filters['post_id']]);
            })->when(isset($filters['key']), function ($query) use ($filters) {
                return $query->where(['key' => $filters['key']]);
            })->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->postSeo->where('id', $id)->update($data);
    }

    public function updateByParams(array $params, array $data): bool
    {
        return $this->postSeo->where($params)->update($data);
    }

    public function updateOrInsert(array $params, array $data): bool
    {
        $postSeo = $this->postSeo->firstOrNew($params);
        $postSeo->fill($data);
        $postSeo->save();
        return true;
    }

    public function delete(array $params): bool
    {
        return $this->postSeo->where($params)->delete();
    }

}
